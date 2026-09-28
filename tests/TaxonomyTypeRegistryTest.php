<?php

namespace JobMetric\Taxonomy\Tests;

use JobMetric\Taxonomy\Exceptions\TaxonomyTypeNotFoundException;
use JobMetric\Taxonomy\Support\TaxonomyTypeRegistry;
use Tests\TestCase;

class TaxonomyTypeRegistryTest extends TestCase
{
    public function test_it_registers_array_and_fluent_options(): void
    {
        $registry = new TaxonomyTypeRegistry;
        $registry->register('category', [
            'label' => 'Category',
            'hierarchical' => true,
        ])->description('Categories')->export();

        $this->assertTrue($registry->has('category'));
        $this->assertSame(['category'], $registry->values());
        $this->assertSame('Category', $registry->getOption('category', 'label'));
        $this->assertTrue($registry->register('category')->hasHierarchical());
        $this->assertTrue($registry->register('category')->hasExport());
    }

    public function test_it_merges_repeated_registrations_and_appends_collections(): void
    {
        $registry = new TaxonomyTypeRegistry;
        $registry->register('category', ['label' => 'Old'])
            ->translation([]);

        $registry->register('category', ['label' => 'New'])
            ->translation([]);

        $this->assertSame('New', $registry->getOption('category', 'label'));
        $this->assertCount(1, $registry->register('category')->getTranslation());
    }

    public function test_it_throws_for_an_unknown_type(): void
    {
        $this->expectException(TaxonomyTypeNotFoundException::class);

        (new TaxonomyTypeRegistry)->ensureExists('missing');
    }

    public function test_it_can_unregister_and_clear_types(): void
    {
        $registry = new TaxonomyTypeRegistry;
        $registry->register('category');
        $registry->unregister('category');

        $this->assertFalse($registry->has('category'));

        $registry->register('tag');
        $registry->clear();

        $this->assertSame([], $registry->all());
    }
}
