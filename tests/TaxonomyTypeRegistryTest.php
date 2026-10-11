<?php

namespace JobMetric\Taxonomy\Tests;

use JobMetric\Taxonomy\Exceptions\TaxonomyTypeNotFoundException;
use JobMetric\Taxonomy\Support\TaxonomyTypeRegistry;
use Tests\TestCase;

class TaxonomyTypeRegistryTest extends TestCase
{
    public function test_management_capabilities_are_opt_in(): void
    {
        $builder = (new TaxonomyTypeRegistry)->register('category');
        $this->assertFalse($builder->hasSortable());
        $this->assertFalse($builder->isSeoEnabled());
        $this->assertFalse($builder->hasMergeable());
        $this->assertFalse($builder->hasShowUsage());
        $this->assertFalse($builder->hasLeafOnly());
        $this->assertTrue($builder->hasMultipleSelection());
        $this->assertNull($builder->getMaxDepth());
        $this->assertNull($builder->getPermission());
        $this->assertSame([], $builder->getFields());
    }

    public function test_taxonomy_relations_and_list_filters_use_fluent_chains(): void
    {
        $builder = (new TaxonomyTypeRegistry)->register('article')
            ->taxonomy('category', 'categories', true)
            ->filters(['status', 'taxonomy.category', 'metadata.featured']);

        $this->assertSame(['type' => 'category', 'multiple' => true], $builder->getTaxonomyTypes()['categories']);
        $this->assertSame(['status', 'taxonomy.category', 'metadata.featured'], $builder->getFilterFields());
    }

    public function test_invalid_list_filter_fields_are_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new TaxonomyTypeRegistry)->register('article')->filters(['taxonomy.']);
    }

    public function test_failed_registration_does_not_leave_partial_options(): void
    {
        $registry = new TaxonomyTypeRegistry;
        $registry->register('existing')->label('Original');
        foreach (['existing', 'new'] as $type) {
            try {
                $registry->register($type, ['label' => 'Changed', 'max-depth' => 0]);
                $this->fail('Invalid depth was accepted.');
            } catch (\InvalidArgumentException $exception) {
                $this->assertSame('Original', $registry->for('existing')->getLabel());
                $this->assertFalse($registry->has('new'));
            }
        }
    }

    public function test_array_registration_uses_management_chains_and_preserves_fields(): void
    {
        $registry = new TaxonomyTypeRegistry;
        $builder = $registry->register('category', [
            'sortable' => true, 'seo' => true, 'mergeable' => true,
            'show-usage' => true, 'leaf-only' => true, 'multiple-selection' => false,
            'max-depth' => 3, 'permission' => 'admin.categories',
            'public-route' => 'categories.show',
            'custom-fields' => ['color' => ['type' => 'color']],
        ]);
        $registry->register('category', ['custom-fields' => ['summary' => ['type' => 'textarea']]]);
        $this->assertTrue($builder->hasSortable());
        $this->assertTrue($builder->isSeoEnabled());
        $this->assertTrue($builder->hasMergeable());
        $this->assertTrue($builder->hasShowUsage());
        $this->assertTrue($builder->hasLeafOnly());
        $this->assertFalse($builder->hasMultipleSelection());
        $this->assertSame(3, $builder->getMaxDepth());
        $this->assertSame('admin.categories', $builder->getPermission());
        $this->assertSame('categories.show', $builder->getPublicRoute());
        $this->assertSame([], $builder->getFilterFields());
        $this->assertSame(['color', 'summary'], array_keys($builder->getFields()));
        $this->assertFalse($builder->sortable(false)->hasSortable());
    }

    public function test_has_seo_chain_adds_translated_seo_fields(): void
    {
        $builder = (new TaxonomyTypeRegistry)->register('article')->hasSeo();

        $this->assertTrue($builder->isSeoEnabled());
        $this->assertSame(
            ['name', 'meta_title', 'meta_description', 'meta_keywords'],
            $builder->getTranslation()->map(fn ($field) => data_get($field->customField->params, 'uniqName'))->all()
        );
        $this->assertSame('seo_image', $builder->getMedia()->sole()->getCollection());

        $builder->hasSeo(false)->hasSeo();
        $this->assertSame(
            ['name', 'meta_title', 'meta_description', 'meta_keywords'],
            $builder->getTranslation()->map(fn ($field) => data_get($field->customField->params, 'uniqName'))->all()
        );
        $this->assertSame('seo_image', $builder->getMedia()->sole()->getCollection());
    }

    public function test_seo_chain_alias_adds_the_same_capabilities(): void
    {
        $builder = (new TaxonomyTypeRegistry)->register('article')->seo();

        $this->assertTrue($builder->isSeoEnabled());
        $this->assertContains('meta_title', $builder->getTranslation()->map(fn ($field) => data_get($field->customField->params, 'uniqName'))->all());
        $this->assertSame('seo_image', $builder->getMedia()->sole()->getCollection());
    }

    public function test_invalid_array_management_options_cannot_bypass_validation(): void
    {
        foreach ([['max-depth' => 0], ['max-depth' => 101], ['max-depth' => '3'],
            ['sortable' => 'false'], ['permission' => '../admin'], ['public-route' => ''],
            ['custom-fields' => ['invalid.key' => []]],
            ['custom-fields' => ['name' => ['type' => 'script']]]] as $options) {
            try {
                (new TaxonomyTypeRegistry)->register('category', $options);
                $this->fail('Invalid management configuration was accepted.');
            } catch (\InvalidArgumentException $exception) {
                $this->assertNotEmpty($exception->getMessage());
            }
        }
    }

    public function test_administration_options_have_fluent_chains_and_append_row_actions(): void
    {
        $registry = new TaxonomyTypeRegistry;
        $builder = $registry->register('menu')
            ->viewPermission('admin.menus.view')
            ->managePermission('admin.menus.manage')
            ->unlockPermission('admin.taxonomies.unlock')
            ->optimisticLocking()
            ->help()
            ->rowAction([
                'key' => 'items', 'label' => 'menus.configure', 'icon' => 'list-tree',
                'url' => '/admin/taxonomies/menu_:id',
            ]);
        $builder->rowAction([
            'key' => 'preview', 'label' => 'menus.preview', 'icon' => 'eye',
            'url' => '/admin/taxonomies/menu_:id/preview',
        ]);

        $this->assertSame('admin.menus.view', $builder->getViewPermission());
        $this->assertSame('admin.menus.manage', $builder->getManagePermission());
        $this->assertSame('admin.taxonomies.unlock', $builder->getUnlockPermission());
        $this->assertTrue($builder->hasOptimisticLocking());
        $this->assertTrue($builder->hasHelp());
        $this->assertSame('admin.taxonomies.unlock', $builder->getUnlockPermission());
        $this->assertCount(2, $builder->getRowActions());
    }

    public function test_base_permission_derives_view_and_manage_permissions(): void
    {
        $builder = (new TaxonomyTypeRegistry)->register('menu')->permission('admin.menus');

        $this->assertSame('admin.menus', $builder->getPermission());
        $this->assertSame('admin.menus.view', $builder->getViewPermission());
        $this->assertSame('admin.menus.manage', $builder->getManagePermission());
    }

    public function test_explicit_administration_permissions_override_the_base_permission(): void
    {
        $builder = (new TaxonomyTypeRegistry)->register('menu')
            ->permission('admin.menus')
            ->viewPermission('admin.content.read')
            ->managePermission('admin.content.write');

        $this->assertSame('admin.content.read', $builder->getViewPermission());
        $this->assertSame('admin.content.write', $builder->getManagePermission());
    }

    public function test_administration_options_are_validated_when_registered_as_an_array(): void
    {
        $registry = new TaxonomyTypeRegistry;
        $builder = $registry->register('menu', [
            'view-permission' => 'admin.menus.view',
            'manage-permission' => 'admin.menus.manage',
            'unlock-permission' => 'admin.taxonomies.unlock',
            'optimistic-locking' => true,
            'help' => true,
            'back-url' => '/admin/taxonomies/menu',
            'row-actions' => [[
                'key' => 'items', 'label' => 'menus.configure', 'icon' => 'list-tree',
                'url' => '/admin/taxonomies/menu_:id',
            ]],
        ]);

        $this->assertTrue($builder->hasOptimisticLocking());
        $this->assertTrue($builder->hasHelp());
        $this->assertSame('/admin/taxonomies/menu', $builder->getBackUrl());
        $this->assertCount(1, $builder->getRowActions());

        foreach ([
            ['view-permission' => 'admin..menus'],
            ['manage-permission' => ''],
            ['optimistic-locking' => 'yes'],
            ['help' => false],
            ['back-url' => ' '],
            ['row-actions' => [['key' => 'items']]],
        ] as $options) {
            try {
                (new TaxonomyTypeRegistry)->register('invalid', $options);
                $this->fail('Invalid taxonomy administration option was accepted.');
            } catch (\InvalidArgumentException $exception) {
                $this->assertNotEmpty($exception->getMessage());
            }
        }
    }

    public function test_facade_and_model_use_the_same_registry(): void
    {
        $this->assertSame(\JobMetric\Taxonomy\Facades\TaxonomyTypeRegistry::getFacadeRoot(), \JobMetric\Taxonomy\Models\Taxonomy::typeRegistry());
    }

    public function test_prefix_is_applied_once_before_the_ancestor_path(): void
    {
        $registry = new TaxonomyTypeRegistry;
        $this->app->instance(TaxonomyTypeRegistry::class, $registry);
        $registry->register('category')->url('catalog/categories');
        $parent = new class extends \JobMetric\Taxonomy\Models\Taxonomy {
            public function getSlugAttribute(): ?string { return 'parent'; }
        };
        $child = new class extends \JobMetric\Taxonomy\Models\Taxonomy {
            public function getSlugAttribute(): ?string { return 'child'; }
        };
        $parent->type = $child->type = 'category';
        $parent->setRelation('parent', null);
        $child->setRelation('parent', $parent);
        $this->assertSame('catalog/categories/parent/child', $child->getFullUrl());
        $this->assertSame('child', $child->slug);
    }

    public function test_url_chain_enables_url_and_normalizes_its_prefix(): void
    {
        $registry = new TaxonomyTypeRegistry;
        $builder = $registry->register('category')->url('/catalog/categories/');
        $this->assertTrue($builder->hasUrl());
        $this->assertSame('catalog/categories', $builder->getUrlPrefix());
        $registry->register('category', ['url' => '/topics/']);
        $this->assertSame('topics', $builder->getUrlPrefix());
        $builder->url('');
        $this->assertSame('', $builder->getUrlPrefix());
        $this->expectException(\InvalidArgumentException::class);
        $builder->url('../private');
    }

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
