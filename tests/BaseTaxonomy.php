<?php

namespace JobMetric\Taxonomy\Tests;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use JobMetric\Taxonomy\Facades\Taxonomy;
use JobMetric\Taxonomy\Models\Taxonomy as TaxonomyModels;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class BaseTaxonomy extends BaseTestCase
{
    /**
     * create a fake product
     *
     * @return Product
     */
    public function create_product(): Product
    {
        return Product::factory()->create();
    }

    /**
     * create a fake taxonomy
     *
     * @param string $type
     * @param string $name
     * @param bool $status
     *
     * @return Model
     */
    public function create_taxonomy_for_has(string $type, string $name, bool $status = true): Model
    {
        $taxonomy = Taxonomy::store([
            'type' => $type,
            'status' => $status,
            'translation' => [
                'en' => [
                    'name' => $name,
                ],
            ],
        ]);

        return TaxonomyModels::find($taxonomy['data']->id);
    }

    /**
     * create a fake taxonomy
     *
     * @return array
     */
    public function create_taxonomy_product(): array
    {
        return Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => null,
            'ordering' => 1,
            'status' => true,
            'translation' => [
                'en' => [
                    'name' => 'taxonomy name',
                    'description' => 'taxonomy description',
                    'meta_title' => 'taxonomy meta title',
                    'meta_description' => 'taxonomy meta description',
                    'meta_keywords' => 'taxonomy meta keywords',
                ]
            ],
        ]);
    }

    /**
     * create a fake taxonomy
     *
     * @return array
     */
    public function create_taxonomy_product_tag(): array
    {
        return Taxonomy::store([
            'type' => 'product_tag',
            'ordering' => 1,
            'status' => true,
            'translation' => [
                'en' => [
                    'name' => 'tag name',
                    'description' => 'tag description',
                    'meta_title' => 'tag meta title',
                    'meta_description' => 'tag meta description',
                    'meta_keywords' => 'tag meta keywords',
                ]
            ],
        ]);
    }
}
