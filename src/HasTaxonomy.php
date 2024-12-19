<?php

namespace JobMetric\Taxonomy;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use JobMetric\Taxonomy\Exceptions\InvalidTaxonomyTypeInCollectionException;
use JobMetric\Taxonomy\Exceptions\ModelTaxonomyContractNotFoundException;
use JobMetric\Taxonomy\Exceptions\TaxonomyCollectionNotInTaxonomyAllowTypesException;
use JobMetric\Taxonomy\Exceptions\TaxonomyIsDisableException;
use JobMetric\Taxonomy\Exceptions\TaxonomyNotFoundException;
use JobMetric\Taxonomy\Http\Resources\TaxonomyResource;
use JobMetric\Taxonomy\Models\Taxonomy;
use JobMetric\Taxonomy\Models\TaxonomyRelation;
use Throwable;

/**
 * Trait HasTaxonomy
 *
 * @package JobMetric\Taxonomy
 *
 * @property Taxonomy[] $taxonomies
 *
 * @method morphToMany(string $class, string $string, string $string1)
 * @method array taxonomyAllowTypes()
 */
trait HasTaxonomy
{
    /**
     * boot has taxonomy
     *
     * @return void
     * @throws Throwable
     */
    public static function bootHasTaxonomy(): void
    {
        if (!in_array('JobMetric\Taxonomy\Contracts\TaxonomyContract', class_implements(self::class))) {
            throw new ModelTaxonomyContractNotFoundException(self::class);
        }
    }

    /**
     * taxonomy has many relationships
     *
     * @return MorphToMany
     */
    public function taxonomies(): MorphToMany
    {
        return $this->morphToMany(Taxonomy::class, 'taxonomizable', config('taxonomy.tables.taxonomy_relation'))
            ->withPivot('collection')
            ->withTimestamps(['created_at']);
    }

    /**
     * Attach taxonomy
     *
     * @param int $taxonomy_id
     * @param string $collection
     *
     * @return array
     * @throws Throwable
     */
    public function attachTaxonomy(int $taxonomy_id, string $collection): array
    {
        /**
         * @var Taxonomy $taxonomy
         */
        $taxonomy = Taxonomy::find($taxonomy_id);

        if (!$taxonomy) {
            throw new TaxonomyNotFoundException($taxonomy_id);
        }

        if (!$taxonomy->status) {
            throw new TaxonomyIsDisableException($taxonomy_id);
        }

        $taxonomyAllowTypes = $this->taxonomyAllowTypes();

        if (!array_key_exists($collection, $taxonomyAllowTypes)) {
            throw new TaxonomyCollectionNotInTaxonomyAllowTypesException($collection);
        }

        if ($taxonomy->type !== $taxonomyAllowTypes[$collection]['type']) {
            throw new InvalidTaxonomyTypeInCollectionException($taxonomy->type, $collection, $taxonomyAllowTypes[$collection]['type']);
        }

        $multiple = false;
        if (array_key_exists('multiple', $taxonomyAllowTypes[$collection])) {
            if ($taxonomyAllowTypes[$collection]['multiple']) {
                $multiple = true;
            }
        }

        if ($multiple) {
            TaxonomyRelation::query()->updateOrInsert([
                'taxonomy_id' => $taxonomy_id,
                'taxonomizable_id' => $this->id,
                'taxonomizable_type' => get_class($this),
                'collection' => $collection
            ]);
        } else {
            TaxonomyRelation::query()->updateOrInsert([
                'taxonomizable_id' => $this->id,
                'taxonomizable_type' => get_class($this),
                'collection' => $collection
            ], [
                'taxonomy_id' => $taxonomy_id
            ]);
        }

        $taxonomy->load([
            'translations',
            'taxonomyRelations'
        ]);

        return [
            'ok' => true,
            'message' => trans('taxonomy::base.messages.attached'),
            'data' => TaxonomyResource::make($taxonomy),
            'status' => 200
        ];
    }

    /**
     * Attach taxonomies
     *
     * @param array $taxonomy_ids
     * @param string $collection
     *
     * @return array
     * @throws Throwable
     */
    public function attachTaxonomies(array $taxonomy_ids, string $collection): array
    {
        foreach ($taxonomy_ids as $taxonomy_id) {
            $this->attachTaxonomy($taxonomy_id, $collection);
        }

        return [
            'ok' => true,
            'message' => trans('taxonomy::base.messages.multi_attached'),
            'status' => 200
        ];
    }

    /**
     * Detach taxonomy
     *
     * @param int $taxonomy_id
     *
     * @return array
     * @throws Throwable
     */
    public function detachTaxonomy(int $taxonomy_id): array
    {
        foreach ($this->taxonomies as $taxonomy) {
            if ($taxonomy->id == $taxonomy_id) {
                $data = TaxonomyResource::make($taxonomy);

                $this->taxonomies()->detach($taxonomy_id);

                return [
                    'ok' => true,
                    'message' => trans('taxonomy::base.messages.detached'),
                    'data' => $data,
                    'status' => 200
                ];
            }
        }

        throw new TaxonomyNotFoundException($taxonomy_id);
    }

    /**
     * Get taxonomy by collection
     *
     * @param string $collection
     *
     * @return MorphToMany
     */
    public function getTaxonomyByCollection(string $collection): MorphToMany
    {
        return $this->taxonomies()->wherePivot('collection', $collection);
    }
}
