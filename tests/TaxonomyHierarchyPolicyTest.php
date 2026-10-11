<?php

namespace JobMetric\Taxonomy\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use JobMetric\Taxonomy\Facades\TaxonomyTypeRegistry;
use JobMetric\Taxonomy\Support\TaxonomyHierarchyPolicy;
use Tests\TestCase;

class TaxonomyHierarchyPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'taxonomy_policy_test',
            'database.connections.taxonomy_policy_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            'taxonomy.tables.taxonomy' => 'policy_nodes',
            'taxonomy.tables.taxonomy_relation' => 'policy_relations',
        ]);
        Schema::create('policy_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('parent_id')->nullable();
        });
        Schema::create('policy_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('taxonomy_id');
        });
        TaxonomyTypeRegistry::register('policy_category')->hierarchical()->maxDepth(3);
        DB::table('policy_nodes')->insert([
            ['id' => 1, 'type' => 'policy_category', 'parent_id' => null],
            ['id' => 2, 'type' => 'policy_category', 'parent_id' => 1],
            ['id' => 3, 'type' => 'policy_category', 'parent_id' => 2],
            ['id' => 4, 'type' => 'policy_category', 'parent_id' => null],
            ['id' => 5, 'type' => 'other_category', 'parent_id' => null],
        ]);
    }

    public function test_a_subtree_can_move_when_its_deepest_child_fits(): void
    {
        app(TaxonomyHierarchyPolicy::class)->validate('policy_category', 2, 4);
        $this->assertSame(1, DB::table('policy_nodes')->where('id', 2)->value('parent_id'));
    }

    public function test_a_subtree_cannot_exceed_the_depth_limit(): void
    {
        $this->expectException(ValidationException::class);
        app(TaxonomyHierarchyPolicy::class)->validate('policy_category', 1, 4);
    }

    public function test_new_children_obey_the_depth_limit(): void
    {
        $this->expectException(ValidationException::class);
        app(TaxonomyHierarchyPolicy::class)->validate('policy_category', null, 3);
    }

    public function test_cycles_self_parenting_and_cross_type_parents_are_rejected(): void
    {
        foreach ([1, 3, 5, 999] as $parent) {
            try {
                app(TaxonomyHierarchyPolicy::class)->validate('policy_category', 1, $parent);
                $this->fail('An invalid parent was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('parent_id', $exception->errors());
            }
        }
    }

    public function test_a_used_leaf_cannot_become_a_parent(): void
    {
        TaxonomyTypeRegistry::for('policy_category')->leafOnly();
        DB::table('policy_relations')->insert(['taxonomy_id' => 4]);
        $this->expectException(ValidationException::class);
        app(TaxonomyHierarchyPolicy::class)->validate('policy_category', 2, 4);
    }
}
