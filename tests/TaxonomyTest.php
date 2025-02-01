<?php

namespace JobMetric\Taxonomy\Tests;

use JobMetric\Taxonomy\Exceptions\CannotMakeParentSubsetOwnChild;
use JobMetric\Taxonomy\Exceptions\TaxonomyNotFoundException;
use JobMetric\Taxonomy\Exceptions\TaxonomyUsedException;
use JobMetric\Taxonomy\Facades\Taxonomy;
use JobMetric\Taxonomy\Http\Resources\TaxonomyRelationResource;
use JobMetric\Taxonomy\Http\Resources\TaxonomyResource;
use Throwable;

class TaxonomyTest extends BaseTaxonomy
{
    /**
     * @throws Throwable
     */
    public function test_store()
    {
        // store taxonomy
        $taxonomy = $this->create_taxonomy_product();

        $this->assertIsArray($taxonomy);
        $this->assertTrue($taxonomy['ok']);
        $this->assertEquals($taxonomy['message'], trans('taxonomy::base.messages.created'));
        $this->assertInstanceOf(TaxonomyResource::class, $taxonomy['data']);
        $this->assertEquals(201, $taxonomy['status']);

        $this->assertDatabaseHas('taxonomies', [
            'type' => 'product_category',
            'parent_id' => null,
            'ordering' => 1,
            'status' => true,
        ]);

        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomy['data']->id,
            'path_id' => $taxonomy['data']->id,
            'level' => 0,
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_type' => 'JobMetric\Taxonomy\Models\Taxonomy',
            'translatable_id' => $taxonomy['data']->id,
            'locale' => 'en',
            'key' => 'name',
            'value' => 'taxonomy name',
        ]);

        // store duplicate name
        $taxonomyDuplicate = $this->create_taxonomy_product();

        $this->assertIsArray($taxonomyDuplicate);
        $this->assertFalse($taxonomyDuplicate['ok']);
        $this->assertEquals($taxonomyDuplicate['message'], trans('package-core::base.validation.errors'));
        $this->assertEquals(422, $taxonomyDuplicate['status']);

        // store with parent taxonomy
        $parentTaxonomy = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomy['data']->id,
            'ordering' => 1,
            'status' => true,
            'translation' => [
                'en' => [
                    'name' => 'taxonomy name',
                    'description' => 'taxonomy description',
                    'meta_title' => 'taxonomy meta title',
                    'meta_description' => 'taxonomy meta description',
                    'meta_keywords' => 'taxonomy meta keywords',
                ],
            ],
        ]);

        $this->assertIsArray($parentTaxonomy);
        $this->assertTrue($parentTaxonomy['ok']);
        $this->assertEquals($parentTaxonomy['message'], trans('taxonomy::base.messages.created'));
        $this->assertInstanceOf(TaxonomyResource::class, $parentTaxonomy['data']);
        $this->assertEquals(201, $parentTaxonomy['status']);

        // store duplicate name with parent taxonomy
        $taxonomyDuplicate = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomy['data']->id,
            'ordering' => 1,
            'status' => true,
            'translation' => [
                'en' => [
                    'name' => 'taxonomy name',
                    'description' => 'taxonomy description',
                    'meta_title' => 'taxonomy meta title',
                    'meta_description' => 'taxonomy meta description',
                    'meta_keywords' => 'taxonomy meta keywords',
                ],
            ],
        ]);

        $this->assertIsArray($taxonomyDuplicate);
        $this->assertFalse($taxonomyDuplicate['ok']);
        $this->assertEquals($taxonomyDuplicate['message'], trans('package-core::base.validation.errors'));
        $this->assertEquals(422, $taxonomyDuplicate['status']);

        // store product tag taxonomy
        $taxonomyProductTag = $this->create_taxonomy_product_tag();

        $this->assertIsArray($taxonomyProductTag);
        $this->assertTrue($taxonomyProductTag['ok']);
        $this->assertEquals($taxonomyProductTag['message'], trans('taxonomy::base.messages.created'));
        $this->assertInstanceOf(TaxonomyResource::class, $taxonomyProductTag['data']);
        $this->assertEquals(201, $taxonomyProductTag['status']);

        $this->assertDatabaseMissing('taxonomy_paths', [
            'type' => 'product_tag',
            'taxonomy_id' => $taxonomyProductTag['data']->id,
            'path_id' => $taxonomyProductTag['data']->id,
            'level' => 0,
        ]);

        // duplicate product tag taxonomy
        $taxonomyDuplicate = $this->create_taxonomy_product_tag();

        $this->assertIsArray($taxonomyDuplicate);
        $this->assertFalse($taxonomyDuplicate['ok']);
        $this->assertEquals($taxonomyDuplicate['message'], trans('package-core::base.validation.errors'));
        $this->assertEquals(422, $taxonomyDuplicate['status']);

        // store product tag taxonomy with parent taxonomy
        $parentTaxonomy = Taxonomy::store([
            'type' => 'product_tag',
            'parent_id' => $taxonomyProductTag['data']->id,
            'ordering' => 1,
            'status' => true,
            'translation' => [
                'en' => [
                    'name' => 'taxonomy name',
                    'description' => 'taxonomy description',
                    'meta_title' => 'taxonomy meta title',
                    'meta_description' => 'taxonomy meta description',
                    'meta_keywords' => 'taxonomy meta keywords',
                ],
            ],
        ]);

        $this->assertIsArray($parentTaxonomy);
        $this->assertTrue($parentTaxonomy['ok']);
        $this->assertEquals($parentTaxonomy['message'], trans('taxonomy::base.messages.created'));
        $this->assertInstanceOf(TaxonomyResource::class, $parentTaxonomy['data']);
        $this->assertEquals(201, $parentTaxonomy['status']);

        $this->assertDatabaseHas('taxonomies', [
            'type' => 'product_tag',
            'parent_id' => null,
            'ordering' => 1,
            'status' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_update()
    {
        // taxonomy not found
        try {
            $taxonomy = Taxonomy::update(1000, [
                'ordering' => 1000,
                'status' => true,
                'translation' => [
                    'en' => [
                        'name' => 'taxonomy name',
                        'description' => 'taxonomy description',
                        'meta_title' => 'taxonomy meta title',
                        'meta_description' => 'taxonomy meta description',
                        'meta_keywords' => 'taxonomy meta keywords',
                    ],
                ],
            ]);

            $this->assertIsArray($taxonomy);
        } catch (Throwable $e) {
            $this->assertInstanceOf(TaxonomyNotFoundException::class, $e);
        }

        /**
         * store taxonomy - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 5  - |__ E
         */
        $taxonomyA = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => null,
            'translation' => [
                'en' => [
                    'name' => 'A',
                ],
            ],
        ]);

        // check database taxonomy path
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyA['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);

        $taxonomyB = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyA['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'B',
                ],
            ],
        ]);

        // check database taxonomy path
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyB['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyB['data']->id,
            'path_id' => $taxonomyB['data']->id,
            'level' => 1,
        ]);

        $taxonomyC = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyB['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'C',
                ],
            ],
        ]);

        // check database taxonomy path
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyC['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyC['data']->id,
            'path_id' => $taxonomyB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyC['data']->id,
            'path_id' => $taxonomyC['data']->id,
            'level' => 2,
        ]);

        $taxonomyD = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyC['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'D',
                ],
            ],
        ]);

        // check database taxonomy path
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyC['data']->id,
            'level' => 2,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyD['data']->id,
            'level' => 3,
        ]);

        $taxonomyE = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyA['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'E',
                ],
            ],
        ]);

        // check database taxonomy path
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyE['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyE['data']->id,
            'path_id' => $taxonomyE['data']->id,
            'level' => 1,
        ]);

        /**
         * update taxonomy move C to E - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 5  - |__ E
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         */
        $taxonomyUpdate = Taxonomy::update($taxonomyC['data']->id, [
            'parent_id' => $taxonomyE['data']->id
        ]);

        $this->assertIsArray($taxonomyUpdate);
        $this->assertTrue($taxonomyUpdate['ok']);
        $this->assertEquals($taxonomyUpdate['message'], trans('taxonomy::base.messages.updated'));
        $this->assertInstanceOf(TaxonomyResource::class, $taxonomyUpdate['data']);
        $this->assertEquals(200, $taxonomyUpdate['status']);

        // check database taxonomy path for C
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyC['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyC['data']->id,
            'path_id' => $taxonomyE['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyC['data']->id,
            'path_id' => $taxonomyC['data']->id,
            'level' => 2,
        ]);

        // check database taxonomy path for D
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyE['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyC['data']->id,
            'level' => 2,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyD['data']->id,
            'level' => 3,
        ]);

        /**
         * update taxonomy move B to E - use sample map
         *
         * 1  - A
         * 5  - |__ E
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 2  - |__ |__ B
         */
        $taxonomyUpdate = Taxonomy::update($taxonomyB['data']->id, [
            'parent_id' => $taxonomyE['data']->id
        ]);

        $this->assertIsArray($taxonomyUpdate);
        $this->assertTrue($taxonomyUpdate['ok']);
        $this->assertEquals($taxonomyUpdate['message'], trans('taxonomy::base.messages.updated'));
        $this->assertInstanceOf(TaxonomyResource::class, $taxonomyUpdate['data']);
        $this->assertEquals(200, $taxonomyUpdate['status']);

        // check database taxonomy path for B
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyB['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyB['data']->id,
            'path_id' => $taxonomyE['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyB['data']->id,
            'path_id' => $taxonomyB['data']->id,
            'level' => 2,
        ]);

        /**
         * update taxonomy move C to A - use sample map
         *
         * 1  - A
         * 5  - |__ E
         * 2  - |__ |__ B
         * 3  - |__ C
         * 4  - |__ |__ D
         */
        $taxonomyUpdate = Taxonomy::update($taxonomyC['data']->id, [
            'parent_id' => $taxonomyA['data']->id
        ]);

        $this->assertIsArray($taxonomyUpdate);
        $this->assertTrue($taxonomyUpdate['ok']);
        $this->assertEquals($taxonomyUpdate['message'], trans('taxonomy::base.messages.updated'));
        $this->assertInstanceOf(TaxonomyResource::class, $taxonomyUpdate['data']);
        $this->assertEquals(200, $taxonomyUpdate['status']);

        // check database taxonomy path for C
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyC['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyC['data']->id,
            'path_id' => $taxonomyC['data']->id,
            'level' => 1,
        ]);

        // check database taxonomy path for D
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyC['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyD['data']->id,
            'level' => 2,
        ]);

        // Entering an illegitimate relationship from A to B
        try {
            $taxonomyUpdate = Taxonomy::update($taxonomyA['data']->id, [
                'parent_id' => $taxonomyB['data']->id
            ]);

            $this->assertIsArray($taxonomyUpdate);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CannotMakeParentSubsetOwnChild::class, $e);
        }

        // Test Full translation
        $taxonomyUpdate = Taxonomy::update($taxonomyA['data']->id, [
            'translation' => [
                'en' => [
                    'name' => 'taxonomy name updated',
                    'description' => 'taxonomy description updated',
                    'meta_title' => 'taxonomy meta title updated',
                    'meta_description' => 'taxonomy meta description updated',
                    'meta_keywords' => 'taxonomy meta keywords updated',
                ],
            ],
        ]);

        $this->assertIsArray($taxonomyUpdate);
        $this->assertTrue($taxonomyUpdate['ok']);
        $this->assertEquals($taxonomyUpdate['message'], trans('taxonomy::base.messages.updated'));
        $this->assertInstanceOf(TaxonomyResource::class, $taxonomyUpdate['data']);

        $this->assertDatabaseHas('translations', [
            'translatable_type' => 'JobMetric\Taxonomy\Models\Taxonomy',
            'translatable_id' => $taxonomyA['data']->id,
            'locale' => app()->getLocale(),
            'key' => 'name',
            'value' => 'taxonomy name updated',
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_type' => 'JobMetric\Taxonomy\Models\Taxonomy',
            'translatable_id' => $taxonomyA['data']->id,
            'locale' => app()->getLocale(),
            'key' => 'description',
            'value' => 'taxonomy description updated',
        ]);

        // Store product tag taxonomy
        $taxonomyProductTag = $this->create_taxonomy_product_tag();

        // Update product tag taxonomy
        $taxonomyProductTagUpdate = Taxonomy::update($taxonomyProductTag['data']->id, [
            'translation' => [
                'en' => [
                    'name' => 'taxonomy tag name updated',
                    'description' => 'taxonomy tag description updated',
                    'meta_title' => 'taxonomy tag meta title updated',
                    'meta_description' => 'taxonomy tag meta description updated',
                    'meta_keywords' => 'taxonomy tag meta keywords updated',
                ],
            ],
        ]);

        $this->assertIsArray($taxonomyProductTagUpdate);
        $this->assertTrue($taxonomyProductTagUpdate['ok']);
        $this->assertEquals($taxonomyProductTagUpdate['message'], trans('taxonomy::base.messages.updated'));
        $this->assertInstanceOf(TaxonomyResource::class, $taxonomyProductTagUpdate['data']);

        $this->assertDatabaseHas('translations', [
            'translatable_type' => 'JobMetric\Taxonomy\Models\Taxonomy',
            'translatable_id' => $taxonomyProductTag['data']->id,
            'locale' => app()->getLocale(),
            'key' => 'name',
            'value' => 'taxonomy tag name updated',
        ]);

        $this->assertDatabaseMissing('translations', [
            'translatable_type' => 'JobMetric\Taxonomy\Models\Taxonomy',
            'translatable_id' => $taxonomyProductTag['data']->id,
            'locale' => app()->getLocale(),
            'key' => 'description',
            'value' => 'taxonomy description updated',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_delete()
    {
        // taxonomy not found
        try {
            $taxonomy = Taxonomy::delete(1000);

            $this->assertIsArray($taxonomy);
        } catch (Throwable $e) {
            $this->assertInstanceOf(TaxonomyNotFoundException::class, $e);
        }

        /**
         * store taxonomy - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 5  - |__ E
         */
        $taxonomyA = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => null,
            'translation' => [
                'en' => [
                    'name' => 'A'
                ],
            ],
        ]);

        // check database taxonomy path
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyA['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);

        $taxonomyB = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyA['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'B'
                ],
            ],
        ]);

        // check database taxonomy path
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyB['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyB['data']->id,
            'path_id' => $taxonomyB['data']->id,
            'level' => 1,
        ]);

        $taxonomyC = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyB['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'C'
                ],
            ],
        ]);

        // check database taxonomy path
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyC['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyC['data']->id,
            'path_id' => $taxonomyB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyC['data']->id,
            'path_id' => $taxonomyC['data']->id,
            'level' => 2,
        ]);

        $taxonomyD = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyC['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'D'
                ],
            ],
        ]);

        // check database taxonomy path
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyC['data']->id,
            'level' => 2,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyD['data']->id,
            'path_id' => $taxonomyD['data']->id,
            'level' => 3,
        ]);

        $taxonomyE = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyA['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'E'
                ],
            ],
        ]);

        // check database taxonomy path
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyE['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyE['data']->id,
            'path_id' => $taxonomyE['data']->id,
            'level' => 1,
        ]);

        /**
         * Delete taxonomy E - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 5  - |__ E -> delete
         */
        $taxonomyDelete = Taxonomy::delete($taxonomyE['data']->id);

        $this->assertIsArray($taxonomyDelete);
        $this->assertTrue($taxonomyDelete['ok']);
        $this->assertEquals($taxonomyDelete['message'], trans('taxonomy::base.messages.deleted'));
        $this->assertInstanceOf(TaxonomyResource::class, $taxonomyDelete['data']);
        $this->assertEquals(200, $taxonomyDelete['status']);

        // check database taxonomy path for E
        $this->assertDatabaseMissing('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyE['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseMissing('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyE['data']->id,
            'path_id' => $taxonomyE['data']->id,
            'level' => 1,
        ]);

        /**
         * attach product to D - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D -> attach product
         */
        $product = $this->create_product();

        $product->attachTaxonomy($taxonomyD['data']->id, 'category');

        /**
         * delete taxonomy C for error used exception - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C -> delete
         * 4  - |__ |__ |__ D -> attach product
         */
        try {
            $taxonomyDelete = Taxonomy::delete($taxonomyC['data']->id);

            $this->assertIsArray($taxonomyDelete);
        } catch (Throwable $e) {
            $this->assertInstanceOf(TaxonomyUsedException::class, $e);
        }

        /**
         * detach product D - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D -> detach product
         */
        $product->detachTaxonomy($taxonomyD['data']->id);

        /**
         * Delete taxonomy C - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C -> delete
         * 4  - |__ |__ |__ D
         */
        $taxonomyDelete = Taxonomy::delete($taxonomyC['data']->id);

        $this->assertIsArray($taxonomyDelete);
        $this->assertTrue($taxonomyDelete['ok']);
        $this->assertEquals($taxonomyDelete['message'], trans('taxonomy::base.messages.deleted'));
        $this->assertInstanceOf(TaxonomyResource::class, $taxonomyDelete['data']);
        $this->assertEquals(200, $taxonomyDelete['status']);

        /**
         * delete taxonomy C for not found error - use sample map
         *
         * 1  - A
         * 2  - |__ B
         */
        try {
            $taxonomyDelete = Taxonomy::delete($taxonomyC['data']->id);

            $this->assertIsArray($taxonomyDelete);
        } catch (Throwable $e) {
            $this->assertInstanceOf(TaxonomyNotFoundException::class, $e);
        }

        /**
         * Delete taxonomy A - use sample map
         *
         * 1  - A -> delete
         * 2  - |__ B
         */
        $taxonomyDelete = Taxonomy::delete($taxonomyA['data']->id);

        $this->assertIsArray($taxonomyDelete);
        $this->assertTrue($taxonomyDelete['ok']);
        $this->assertEquals($taxonomyDelete['message'], trans('taxonomy::base.messages.deleted'));
        $this->assertInstanceOf(TaxonomyResource::class, $taxonomyDelete['data']);
        $this->assertEquals(200, $taxonomyDelete['status']);

        // check database taxonomy path for A
        $this->assertDatabaseMissing('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyA['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);

        // check database taxonomy path for B
        $this->assertDatabaseMissing('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyB['data']->id,
            'path_id' => $taxonomyA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseMissing('taxonomy_paths', [
            'type' => 'product_category',
            'taxonomy_id' => $taxonomyB['data']->id,
            'path_id' => $taxonomyB['data']->id,
            'level' => 1,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_get_name()
    {
        // taxonomy not found
        try {
            $taxonomy = Taxonomy::delete(1000);

            $this->assertIsArray($taxonomy);
        } catch (Throwable $e) {
            $this->assertInstanceOf(TaxonomyNotFoundException::class, $e);
        }

        /**
         * store taxonomy - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         */
        $taxonomyA = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => null,
            'translation' => [
                'en' => [
                    'name' => 'A'
                ],
            ],
        ]);

        $taxonomyB = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyA['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'B'
                ],
            ],
        ]);

        $taxonomyC = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyB['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'C'
                ],
            ],
        ]);

        $taxonomyD = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyC['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'D'
                ],
            ],
        ]);

        // get name of taxonomy D full path
        $taxonomyName = Taxonomy::getName($taxonomyD['data']->id);

        $this->assertIsString($taxonomyName);
        $this->assertEquals('A ► B ► C ► D', $taxonomyName);

        // get name of taxonomy D single name
        $taxonomyName = Taxonomy::getName($taxonomyD['data']->id, false);

        $this->assertIsString($taxonomyName);
        $this->assertEquals('D', $taxonomyName);
    }

    /**
     * @throws Throwable
     */
    public function test_all()
    {
        /**
         * store product taxonomy - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 5  - |__ E
         */
        $taxonomyA = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => null,
            'translation' => [
                'en' => [
                    'name' => 'A'
                ],
            ],
        ]);

        $taxonomyB = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyA['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'B'
                ],
            ],
        ]);

        $taxonomyC = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyB['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'C'
                ],
            ],
        ]);

        Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyC['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'D'
                ],
            ],
        ]);

        Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyA['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'E'
                ],
            ],
        ]);

        $productCategories = Taxonomy::all('product_category');

        $this->assertCount(5, $productCategories);

        $productCategories->each(function ($productTaxonomy) {
            $this->assertInstanceOf(TaxonomyResource::class, $productTaxonomy);
        });

        $char = config('taxonomy.arrow_icon.' . trans('domi::base.direction'));

        $this->assertEquals('A', $productCategories[0]->name);
        $this->assertArrayHasKey('name_multiple', $productCategories[0]);
        $this->assertEquals('A', $productCategories[0]->name_multiple);

        $this->assertEquals('B', $productCategories[1]->name);
        $this->assertArrayHasKey('name_multiple', $productCategories[1]);
        $this->assertEquals('A' . $char . 'B', $productCategories[1]->name_multiple);

        $this->assertEquals('C', $productCategories[2]->name);
        $this->assertArrayHasKey('name_multiple', $productCategories[2]);
        $this->assertEquals('A' . $char . 'B' . $char . 'C', $productCategories[2]->name_multiple);

        $this->assertEquals('D', $productCategories[3]->name);
        $this->assertArrayHasKey('name_multiple', $productCategories[3]);
        $this->assertEquals('A' . $char . 'B' . $char . 'C' . $char . 'D', $productCategories[3]->name_multiple);

        $this->assertEquals('E', $productCategories[4]->name);
        $this->assertArrayHasKey('name_multiple', $productCategories[4]);
        $this->assertEquals('A' . $char . 'E', $productCategories[4]->name_multiple);

        /**
         * store product tag - use sample map
         *
         * 1  - A
         * 2  - B
         * 3  - C
         */
        Taxonomy::store([
            'type' => 'product_tag',
            'translation' => [
                'en' => [
                    'name' => 'A'
                ],
            ],
        ]);

        Taxonomy::store([
            'type' => 'product_tag',
            'translation' => [
                'en' => [
                    'name' => 'B'
                ],
            ],
        ]);

        Taxonomy::store([
            'type' => 'product_tag',
            'translation' => [
                'en' => [
                    'name' => 'C'
                ],
            ],
        ]);

        $productTags = Taxonomy::all('product_tag');

        $this->assertCount(3, $productTags);

        $productTags->each(function ($productTag) {
            $this->assertInstanceOf(TaxonomyResource::class, $productTag);
        });

        $this->assertEquals('A', $productTags[0]->name);
        $this->assertArrayNotHasKey('name_multiple', $productTags[0]);

        $this->assertEquals('B', $productTags[1]->name);
        $this->assertArrayNotHasKey('name_multiple', $productTags[1]);

        $this->assertEquals('C', $productTags[2]->name);
        $this->assertArrayNotHasKey('name_multiple', $productTags[2]);
    }

    /**
     * @throws Throwable
     */
    public function test_pagination()
    {
        /**
         * store product taxonomy - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 5  - |__ E
         */
        $taxonomyA = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => null,
            'translation' => [
                'en' => [
                    'name' => 'A'
                ],
            ],
        ]);

        $taxonomyB = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyA['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'B'
                ],
            ],
        ]);

        $taxonomyC = Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyB['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'C'
                ],
            ],
        ]);

        Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyC['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'D'
                ],
            ],
        ]);

        Taxonomy::store([
            'type' => 'product_category',
            'parent_id' => $taxonomyA['data']->id,
            'translation' => [
                'en' => [
                    'name' => 'E'
                ],
            ],
        ]);

        $paginateProductCategories = Taxonomy::paginate('product_category');

        $paginateProductCategories->each(function ($paginateProductTaxonomy) {
            $this->assertInstanceOf(TaxonomyResource::class, $paginateProductTaxonomy);
        });

        $this->assertIsInt($paginateProductCategories->total());
        $this->assertIsInt($paginateProductCategories->perPage());
        $this->assertIsInt($paginateProductCategories->currentPage());
        $this->assertIsInt($paginateProductCategories->lastPage());
        $this->assertIsArray($paginateProductCategories->items());
    }

    /**
     * @throws Throwable
     */
    public function test_used_in()
    {
        $product = $this->create_product();

        // Store a taxonomy
        $taxonomy_store = $this->create_taxonomy_product();

        // Attach the taxonomy to the product
        $product->attachTaxonomy($taxonomy_store['data']->id, 'category');

        // Get the taxonomy used in the product
        $used_in = Taxonomy::usedIn($taxonomy_store['data']->id);

        $this->assertIsArray($used_in);
        $this->assertTrue($used_in['ok']);
        $this->assertEquals($used_in['message'], trans('taxonomy::base.messages.used_in', [
            'count' => 1
        ]));
        $used_in['data']->each(function ($dataUsedIn) {
            $this->assertInstanceOf(TaxonomyRelationResource::class, $dataUsedIn);
        });
        $this->assertEquals(200, $used_in['status']);

        // Get the taxonomy used in the product with a wrong taxonomy id
        try {
            $used_in = Taxonomy::usedIn(1000);

            $this->assertIsArray($used_in);
        } catch (Throwable $e) {
            $this->assertInstanceOf(TaxonomyNotFoundException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function test_has_used()
    {
        $product = $this->create_product();

        // Store a taxonomy
        $taxonomyStore = $this->create_taxonomy_product();

        // Attach the taxonomy to the product
        $product->attachTaxonomy($taxonomyStore['data']->id, 'category');

        // check has used in
        $usedIn = Taxonomy::hasUsed($taxonomyStore['data']->id);

        $this->assertTrue($usedIn);

        // check with wrong taxonomy id
        try {
            $usedIn = Taxonomy::hasUsed(1000);

            $this->assertIsArray($usedIn);
        } catch (Throwable $e) {
            $this->assertInstanceOf(TaxonomyNotFoundException::class, $e);
        }
    }
}
