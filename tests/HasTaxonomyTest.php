<?php

namespace JobMetric\Taxonomy\Tests;

use App\Models\Product;
use JobMetric\Taxonomy\Exceptions\TaxonomyCollectionNotInTaxonomyAllowTypesException;
use JobMetric\Taxonomy\Exceptions\TaxonomyIsDisableException;
use JobMetric\Taxonomy\Exceptions\InvalidTaxonomyTypeInCollectionException;
use JobMetric\Taxonomy\Http\Resources\TaxonomyResource;
use JobMetric\Taxonomy\Models\Taxonomy;
use Throwable;

class HasTaxonomyTest extends BaseTaxonomy
{
    /**
     * @throws Throwable
     */
    public function test_taxonomies_trait_relationship()
    {
        $product = new Product();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $product->taxonomies());
    }

    /**
     * @throws Throwable
     */
    public function test_attach_taxonomy(): void
    {
        /**
         * @var Product $product
         * @var Taxonomy $taxonomy_product_1
         * @var Taxonomy $taxonomy_product_2
         * @var Taxonomy $taxonomy_product_tag_1
         * @var Taxonomy $taxonomy_product_tag_2
         * @var Taxonomy $taxonomy_product_tag_3
         */
        $product = $this->create_product();
        $taxonomy_product_1 = $this->create_taxonomy_for_has('product_category', 'product 1');
        $taxonomy_product_2 = $this->create_taxonomy_for_has('product_category', 'product 2');
        $taxonomy_product_tag_1 = $this->create_taxonomy_for_has('product_tag', 'product tag 1');
        $taxonomy_product_tag_2 = $this->create_taxonomy_for_has('product_tag', 'product tag 2');
        $taxonomy_product_tag_3 = $this->create_taxonomy_for_has('product_tag', 'product tag 3', false);

        // attach normally single collection
        $attach_1 = $product->attachTaxonomy($taxonomy_product_1->id, 'category');

        $this->assertIsArray($attach_1);
        $this->assertTrue($attach_1['ok']);
        $this->assertEquals($attach_1['message'], trans('taxonomy::base.messages.attached'));
        $this->assertInstanceOf(TaxonomyResource::class, $attach_1['data']);
        $this->assertEquals(200, $attach_1['status']);

        $this->assertDatabaseHas(config('taxonomy.tables.taxonomy_relation'), [
            'taxonomy_id' => $taxonomy_product_1->id,
            'taxonomizable_id' => $product->id,
            'taxonomizable_type' => Product::class,
            'collection' => 'category'
        ]);

        $attach_2 = $product->attachTaxonomy($taxonomy_product_2->id, 'category');

        $this->assertIsArray($attach_2);
        $this->assertTrue($attach_2['ok']);
        $this->assertEquals($attach_2['message'], trans('taxonomy::base.messages.attached'));
        $this->assertInstanceOf(TaxonomyResource::class, $attach_2['data']);
        $this->assertEquals(200, $attach_2['status']);

        $this->assertDatabaseMissing(config('taxonomy.tables.taxonomy_relation'), [
            'taxonomy_id' => $taxonomy_product_1->id,
            'taxonomizable_id' => $product->id,
            'taxonomizable_type' => Product::class,
            'collection' => 'category'
        ]);

        $this->assertDatabaseHas(config('taxonomy.tables.taxonomy_relation'), [
            'taxonomy_id' => $taxonomy_product_2->id,
            'taxonomizable_id' => $product->id,
            'taxonomizable_type' => Product::class,
            'collection' => 'category'
        ]);

        // attach normally multiple collection
        $attach_1 = $product->attachTaxonomy($taxonomy_product_tag_1->id, 'tag');

        $this->assertIsArray($attach_1);
        $this->assertTrue($attach_1['ok']);
        $this->assertEquals($attach_1['message'], trans('taxonomy::base.messages.attached'));
        $this->assertInstanceOf(TaxonomyResource::class, $attach_1['data']);
        $this->assertEquals(200, $attach_1['status']);

        $this->assertDatabaseHas(config('taxonomy.tables.taxonomy_relation'), [
            'taxonomy_id' => $taxonomy_product_tag_1->id,
            'taxonomizable_id' => $product->id,
            'taxonomizable_type' => Product::class,
            'collection' => 'tag'
        ]);

        $attach_2 = $product->attachTaxonomy($taxonomy_product_tag_2->id, 'tag');

        $this->assertIsArray($attach_1);
        $this->assertTrue($attach_2['ok']);
        $this->assertEquals($attach_2['message'], trans('taxonomy::base.messages.attached'));
        $this->assertInstanceOf(TaxonomyResource::class, $attach_2['data']);
        $this->assertEquals(200, $attach_2['status']);

        $this->assertDatabaseHas(config('taxonomy.tables.taxonomy_relation'), [
            'taxonomy_id' => $taxonomy_product_tag_1->id,
            'taxonomizable_id' => $product->id,
            'taxonomizable_type' => Product::class,
            'collection' => 'tag'
        ]);
        $this->assertDatabaseHas(config('taxonomy.tables.taxonomy_relation'), [
            'taxonomy_id' => $taxonomy_product_tag_2->id,
            'taxonomizable_id' => $product->id,
            'taxonomizable_type' => Product::class,
            'collection' => 'tag'
        ]);

        // attach invalid collection
        try {
            $product->attachTaxonomy($taxonomy_product_1->id, 'invalid_collection');
        } catch (Throwable $e) {
            $this->assertInstanceOf(TaxonomyCollectionNotInTaxonomyAllowTypesException::class, $e);
        }

        // attach invalid collection type
        try {
            $product->attachTaxonomy($taxonomy_product_1->id, 'tag');
        } catch (Throwable $e) {
            $this->assertInstanceOf(InvalidTaxonomyTypeInCollectionException::class, $e);
        }

        // attach disabled taxonomy
        try {
            $product->attachTaxonomy($taxonomy_product_tag_3->id, 'tag');
        } catch (Throwable $e) {
            $this->assertInstanceOf(TaxonomyIsDisableException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function test_attach_taxonomies(): void
    {
        /**
         * @var Product $product
         * @var Taxonomy $taxonomy_product_tag_1
         * @var Taxonomy $taxonomy_product_tag_2
         * @var Taxonomy $taxonomy_product_tag_3
         */
        $product = $this->create_product();
        $taxonomy_product_tag_1 = $this->create_taxonomy_for_has('product_tag', 'product tag 1');
        $taxonomy_product_tag_2 = $this->create_taxonomy_for_has('product_tag', 'product tag 2');
        $taxonomy_product_tag_3 = $this->create_taxonomy_for_has('product_tag', 'product tag 3');

        // attach multiple taxonomy
        $attach_1 = $product->attachCategories([
            $taxonomy_product_tag_1->id,
            $taxonomy_product_tag_2->id,
            $taxonomy_product_tag_3->id,
        ], 'tag');

        $this->assertIsArray($attach_1);
        $this->assertTrue($attach_1['ok']);
        $this->assertEquals($attach_1['message'], trans('taxonomy::base.messages.multi_attached'));
        $this->assertEquals(200, $attach_1['status']);

        $this->assertDatabaseHas(config('taxonomy.tables.taxonomy_relation'), [
            'taxonomy_id' => $taxonomy_product_tag_1->id,
            'taxonomizable_id' => $product->id,
            'taxonomizable_type' => Product::class,
            'collection' => 'tag'
        ]);
        $this->assertDatabaseHas(config('taxonomy.tables.taxonomy_relation'), [
            'taxonomy_id' => $taxonomy_product_tag_2->id,
            'taxonomizable_id' => $product->id,
            'taxonomizable_type' => Product::class,
            'collection' => 'tag'
        ]);
        $this->assertDatabaseHas(config('taxonomy.tables.taxonomy_relation'), [
            'taxonomy_id' => $taxonomy_product_tag_3->id,
            'taxonomizable_id' => $product->id,
            'taxonomizable_type' => Product::class,
            'collection' => 'tag'
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_detach_taxonomy(): void
    {
        /**
         * @var Product $product
         * @var Taxonomy $taxonomy_product
         */
        $product = $this->create_product();
        $taxonomy_product = $this->create_taxonomy_for_has('product_category', 'product');

        // attach taxonomy
        $product->attachTaxonomy($taxonomy_product->id, 'category');

        $detach = $product->detachTaxonomy($taxonomy_product->id);

        $this->assertIsArray($detach);

        $this->assertDatabaseMissing(config('taxonomy.tables.taxonomy_relation'), [
            'taxonomy_id' => $taxonomy_product->id,
            'taxonomizable_id' => $product->id,
            'taxonomizable_type' => Product::class,
            'collection' => 'category'
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_get_taxonomy_by_collection(): void
    {
        $product = new Product();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $product->getTaxonomyByCollection('taxonomy'));
    }
}
