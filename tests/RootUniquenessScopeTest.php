<?php

namespace JobMetric\Taxonomy\Tests;

use JobMetric\Taxonomy\Facades\TaxonomyTypeRegistry;
use JobMetric\Taxonomy\Http\Requests\StoreTaxonomyRequest;
use Tests\TestCase;

class RootUniquenessScopeTest extends TestCase
{
    public function test_root_and_child_registration_keep_explicit_parent_scope(): void
    {
        TaxonomyTypeRegistry::register('scope_test')->hierarchical();
        foreach ([null, 12] as $parent) {
            $rules = (new StoreTaxonomyRequest)->setData([
                'type' => 'scope_test', 'parent_id' => $parent,
                'translation' => ['en' => ['name' => 'A']],
            ])->rules();
            $found = false;
            foreach ($rules['translation.en.name'] as $rule) {
                if (!is_object($rule) || !(new \ReflectionClass($rule))->hasProperty('parent_where')) continue;
                $scope = (new \ReflectionProperty($rule, 'parent_where'))->getValue($rule);
                $this->assertArrayHasKey('parent_id', $scope);
                $this->assertSame($parent, $scope['parent_id']);
                $found = true;
            }
            $this->assertTrue($found);
        }
    }
}
