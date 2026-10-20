<?php

namespace JobMetric\Taxonomy;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JobMetric\Taxonomy\Facades\Taxonomy as TaxonomyService;
use JobMetric\Taxonomy\Facades\TaxonomyTypeRegistry;
use JobMetric\Taxonomy\Models\Taxonomy;
use JobMetric\Taxonomy\Support\TaxonomyHierarchyPolicy;
use JobMetric\Url\Models\Url;

/** Atomic administrative moves and merges over the package's existing relations. */
class TaxonomyManagement
{
    public function move(string $type, int $id, ?int $parentId, int $position): Taxonomy
    {
        abort_unless(TaxonomyTypeRegistry::for($type)->hasSortable(), 404);
        return DB::transaction(function () use ($type, $id, $parentId, $position) {
            $nodes = Taxonomy::query()->where('type', $type)->orderBy('id')->lockForUpdate()->get();
            $record = $nodes->firstWhere('id', $id);
            abort_unless($record, 404);
            if (!TaxonomyTypeRegistry::for($type)->hasHierarchical() && $parentId !== null) {
                throw ValidationException::withMessages(['parent_id' => trans('taxonomy::base.management.invalid_parent')]);
            }
            app(TaxonomyHierarchyPolicy::class)->validate($type, $id, $parentId);
            $outcomeBefore = \JobMetric\EventSystem\Support\OutcomeState::capture($record);
            $siblings = $nodes->filter(fn ($item) => $item->parent_id === $parentId && $item->id !== $id)->sortBy('ordering')->values();
            $position = min(max(0, $position), $siblings->count());
            $siblings->splice($position, 0, [$record]);
            $this->update($record, ['parent_id' => $parentId]);
            foreach ($siblings as $index => $sibling) $this->update($sibling, ['ordering' => $index]);

            if ($outcomeBefore !== \JobMetric\EventSystem\Support\OutcomeState::capture($record)) {
                app(\JobMetric\EventSystem\Support\CommittedEventDispatcher::class)->dispatch(new \JobMetric\Taxonomy\Events\Taxonomy\TaxonomyMoveCommittedEvent($record->getKey()), $record->getConnectionName());
            }
            return $record->fresh();
        });
    }

    /** Preserve destination fields, move children and relations, and retain old URL redirects. */
    public function merge(string $type, int $sourceId, int $targetId): Taxonomy
    {
        abort_unless(TaxonomyTypeRegistry::for($type)->hasMergeable(), 404);
        return DB::transaction(function () use ($type, $sourceId, $targetId) {
            $nodes = Taxonomy::query()->where('type', $type)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            abort_unless($nodes->has($sourceId) && $nodes->has($targetId), 404);
            if ($sourceId === $targetId) throw ValidationException::withMessages(['target_id' => trans('taxonomy::base.management.invalid_parent')]);
            $source = $nodes[$sourceId];
            $target = $nodes[$targetId];
            // Merging removes the source level; validate ancestry independently of move depth.
            $cursor = $targetId;
            $seen = [];
            while ($cursor !== null) {
                if ($cursor === $sourceId || isset($seen[$cursor]) || !$nodes->has($cursor)) {
                    throw ValidationException::withMessages(['target_id' => trans('taxonomy::base.management.invalid_parent')]);
                }
                $seen[$cursor] = true;
                $cursor = $nodes[$cursor]->parent_id;
            }
            $children = $nodes->where('parent_id', $sourceId);
            $targetHasChildren = $children->isNotEmpty() || $nodes->where('parent_id', $targetId)->isNotEmpty();
            if (TaxonomyTypeRegistry::for($type)->hasLeafOnly() && $targetHasChildren && ($source->taxonomyRelations()->exists() || $target->taxonomyRelations()->exists())) {
                throw ValidationException::withMessages(['target_id' => trans('taxonomy::base.management.used_leaf')]);
            }
            foreach ($children as $child) $this->update($child, ['parent_id' => $targetId]);
            $table = DB::table(config('taxonomy.tables.taxonomy_relation'));
            foreach ((clone $table)->where('taxonomy_id', $sourceId)->get() as $relation) {
                $key = ['taxonomy_id' => $targetId, 'taxonomizable_type' => $relation->taxonomizable_type, 'taxonomizable_id' => $relation->taxonomizable_id, 'collection' => $relation->collection];
                if (!(clone $table)->where($key)->exists()) (clone $table)->insert($key + ['created_at' => $relation->created_at]);
            }
            (clone $table)->where('taxonomy_id', $sourceId)->delete();
            $version = (int) Url::withTrashed()->ofUrlable($target->getMorphClass(), $targetId)->max('version');
            foreach (Url::withTrashed()->ofUrlable($source->getMorphClass(), $sourceId)->get() as $oldUrl) {
                $oldUrl->urlable_id = $targetId;
                $oldUrl->version = ++$version;
                $oldUrl->deleted_at = now();
                $oldUrl->save();
            }
            TaxonomyService::delete($sourceId);
            if (true) {
                app(\JobMetric\EventSystem\Support\CommittedEventDispatcher::class)->dispatch(new \JobMetric\Taxonomy\Events\Taxonomy\TaxonomyMergeCommittedEvent($sourceId, $targetId), $target->getConnectionName());
            }
            return $target->fresh();
        });
    }

    private function update(Taxonomy $record, array $values): void
    {
        $result = TaxonomyService::update($record->id, $values + ['translation' => $record->getTranslations()]);
        if (!$result['ok']) throw ValidationException::withMessages(['taxonomy' => $result['errors'] ?? [$result['message']]]);
    }
}
