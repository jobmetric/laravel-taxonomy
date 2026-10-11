<?php

namespace JobMetric\Taxonomy\Support;

use Illuminate\Validation\ValidationException;
use JobMetric\Taxonomy\Facades\TaxonomyTypeRegistry;
use JobMetric\Taxonomy\Models\Taxonomy;

/** Validate hierarchy changes against the entire moving subtree. */
class TaxonomyHierarchyPolicy
{
    public function validate(string $type, ?int $id, ?int $parentId): void
    {
        $builder = TaxonomyTypeRegistry::for($type);
        $nodes = Taxonomy::query()->where('type', $type)->get(['id', 'parent_id'])->keyBy('id');
        $depth = 1;
        $seen = [];
        $cursor = $parentId;
        while ($cursor !== null) {
            if ($cursor === $id || isset($seen[$cursor]) || !$nodes->has($cursor)) {
                throw ValidationException::withMessages(['parent_id' => trans('taxonomy::base.management.invalid_parent')]);
            }
            $seen[$cursor] = true;
            $depth++;
            $cursor = $nodes[$cursor]->parent_id;
        }
        if ($parentId && $builder->hasLeafOnly() && Taxonomy::find($parentId)->taxonomyRelations()->exists()) {
            throw ValidationException::withMessages(['parent_id' => trans('taxonomy::base.management.used_leaf')]);
        }
        $children = $nodes->groupBy(fn ($node) => (int) $node->parent_id);
        $height = function (int $nodeId, array $path = []) use (&$height, $children): int {
            if (isset($path[$nodeId])) throw ValidationException::withMessages(['parent_id' => trans('taxonomy::base.management.invalid_parent')]);
            $path[$nodeId] = true;
            $maximum = 0;
            foreach ($children->get($nodeId, collect()) as $child) $maximum = max($maximum, 1 + $height($child->id, $path));
            return $maximum;
        };
        if ($builder->getMaxDepth() !== null && $depth + ($id ? $height($id) : 0) > $builder->getMaxDepth()) {
            throw ValidationException::withMessages(['parent_id' => trans('taxonomy::base.management.max_depth', ['depth' => $builder->getMaxDepth()])]);
        }
    }
}
