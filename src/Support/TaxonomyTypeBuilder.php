<?php

namespace JobMetric\Taxonomy\Support;

use JobMetric\Media\Support\HasMediaType;
use JobMetric\Metadata\Support\HasMetadataType;
use JobMetric\Translation\Support\HasTranslationType;
use JobMetric\Url\Support\HasUrlType;

class TaxonomyTypeBuilder
{
    use HasTranslationType;
    use HasMetadataType;
    use HasMediaType;
    use HasUrlType;
    use HasManagementCapabilities;
    use HasTaxonomyAdministrationOptions;

    public function __construct(
        protected string $type,
        protected TaxonomyTypeRegistry $registry,
    ) {
    }

    public function bootDefaults(): void
    {
        $this->bootTranslationServiceType();
    }

    public function apply(array $options): static
    {
        foreach ($options as $key => $value) {
            $capabilities = [
                'sortable' => 'sortable', 'seo' => 'seo', 'has-seo' => 'hasSeo', 'mergeable' => 'mergeable',
                'show-usage' => 'showUsage', 'multiple-selection' => 'multipleSelection',
                'leaf-only' => 'leafOnly', 'max-depth' => 'maxDepth',
                'permission' => 'permission', 'public-route' => 'publicRoute',
            ];
            if (isset($capabilities[$key])) {
                $expected = match ($key) {
                    'max-depth' => 'integer',
                    'permission', 'public-route' => 'string',
                    default => 'boolean',
                };
                if (gettype($value) !== $expected) {
                    throw new \InvalidArgumentException("Invalid value for taxonomy option {$key}.");
                }
                $this->{$capabilities[$key]}($value);
                continue;
            }
            if ($key === 'filters') {
                if (!is_array($value)) {
                    throw new \InvalidArgumentException('Taxonomy filters must be an array.');
                }
                $this->filters($value);
                continue;
            }
            if ($key === 'custom-fields') {
                if (!is_array($value)) {
                    throw new \InvalidArgumentException('Taxonomy custom fields must be an array.');
                }
                foreach ($value as $field => $definition) {
                    $this->field($field, $definition);
                }
                continue;
            }
            if (in_array($key, ['view-permission', 'manage-permission', 'unlock-permission', 'optimistic-locking', 'help', 'back-url'], true)) {
                if ($key === 'help') {
                    if ($value !== true) {
                        throw new \InvalidArgumentException('The taxonomy help option must be enabled with true.');
                    }
                    $this->help();
                    continue;
                }
                $methods = [
                    'view-permission' => 'viewPermission',
                    'manage-permission' => 'managePermission',
                    'unlock-permission' => 'unlockPermission',
                    'optimistic-locking' => 'optimisticLocking',
                    'back-url' => 'backUrl',
                ];
                $expectedType = $key === 'optimistic-locking' ? 'boolean' : 'string';
                if (gettype($value) !== $expectedType) {
                    throw new \InvalidArgumentException("Invalid value for taxonomy option {$key}.");
                }
                $method = $methods[$key];
                $this->{$method}(...[$value]);
                continue;
            }
            if ($key === 'row-actions') {
                if (!is_array($value)) {
                    throw new \InvalidArgumentException('Taxonomy row actions must be an array.');
                }
                $this->rowActions($value);
                continue;
            }
            if ($key === 'url') {
                if (! is_string($value)) {
                    throw new \InvalidArgumentException('The taxonomy URL prefix must be a string.');
                }
                $this->url($value);
                continue;
            }
            if (in_array($key, ['translation', 'metadata', 'media'], true)) {
                $this->{$key}($value);
                continue;
            }

            $this->registry->mergeOption($this->type, $key, $value);
        }

        return $this;
    }

    public function get(): array
    {
        return $this->registry->get($this->type);
    }

    /** Enable URL support and set the website path prefix for this type. */
    public function url(string $prefix = ''): static
    {
        $prefix = trim($prefix, '/');
        if ($prefix !== '' && !preg_match('~^[\pL\pN_-]+(?:/[\pL\pN_-]+)*$~u', $prefix)) {
            throw new \InvalidArgumentException('The URL prefix must contain path segments only.');
        }
        $this->setTypeParam('hasUrl', true);
        $this->setTypeParam('url-prefix', $prefix);

        return $this;
    }

    /** Return the website path prefix registered for this type. */
    public function getUrlPrefix(): string
    {
        return (string) $this->getTypeParam('url-prefix', '');
    }

    protected function setTypeParam(string $key, mixed $value): void
    {
        $this->registry->setOption($this->type, $key, $value);
    }

    protected function appendTypeParam(string $key, array $values): void
    {
        $this->registry->appendOption($this->type, $key, $values);
    }

    protected function getTypeParam(string $key, mixed $default = null): mixed
    {
        return $this->registry->getOption($this->type, $key, $default);
    }

    public function label(string $label): static { $this->setTypeParam('label', $label); return $this; }
    public function getLabel(): string { return trans($this->getTypeParam('label', '')); }
    public function description(string $description): static { $this->setTypeParam('description', $description); return $this; }
    public function getDescription(): string { return trans($this->getTypeParam('description', '')); }
    public function hierarchical(): static { $this->setTypeParam('hierarchical', true); return $this; }
    public function hasHierarchical(): bool { return (bool) $this->getTypeParam('hierarchical', false); }
    public function taxonomy(string $type, string $collection = 'taxonomies', bool $multiple = true): static
    {
        $taxonomies = (array) $this->getTypeParam('taxonomy-types', []);
        $taxonomies[$collection] = ['type' => $type, 'multiple' => $multiple];
        $this->setTypeParam('taxonomy-types', $taxonomies);

        return $this;
    }
    public function getTaxonomyTypes(): array { return (array) $this->getTypeParam('taxonomy-types', []); }
    public function filters(array $filters): static
    {
        $fields = (array) $this->getTypeParam('filter-fields', []);
        foreach ($filters as $key => $filter) {
            if (is_string($key) && is_array($filter) && isset($filter['label'], $filter['options'], $filter['apply'])) {
                $this->addFilter($key, $filter);
                continue;
            }
            if (is_string($key) && $filter === true) {
                $filter = $key;
            }
            if (!is_string($filter) || !preg_match('/^(status|taxonomy\.[a-z][a-z0-9_-]*|metadata\.[a-z][a-z0-9_-]*)$/', $filter)) {
                throw new \InvalidArgumentException('Taxonomy filter fields must be status, taxonomy types, or metadata keys.');
            }
            $fields[] = $filter;
        }
        $this->setTypeParam('filter-fields', array_values(array_unique($fields)));

        return $this;
    }
    public function getFilterFields(): array { return (array) $this->getTypeParam('filter-fields', []); }
    public function addFilter(string $key, array $definition): static
    {
        if ($key === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
            throw new \InvalidArgumentException('Filter keys must contain lowercase letters, digits, and underscores.');
        }
        if (!is_string($definition['label'] ?? null) || !is_array($definition['options'] ?? null) || !is_callable($definition['apply'] ?? null)) {
            throw new \InvalidArgumentException('A taxonomy filter requires a label, options array, and apply callback.');
        }
        $filters = (array) $this->getTypeParam('custom-filters', []);
        $filters[$key] = $definition;
        $this->setTypeParam('custom-filters', $filters);

        return $this;
    }
    public function getCustomFilters(): array { return (array) $this->getTypeParam('custom-filters', []); }
    public function import(): static { $this->setTypeParam('import', true); return $this; }
    public function hasImport(): bool { return (bool) $this->getTypeParam('import', false); }
    public function export(): static { $this->setTypeParam('export', true); return $this; }
    public function hasExport(): bool { return (bool) $this->getTypeParam('export', false); }
    public function showDescriptionInList(): static { $this->setTypeParam('show-description-in-list', true); return $this; }
    public function hasShowDescriptionInList(): bool { return (bool) $this->getTypeParam('show-description-in-list', false); }
    public function removeFilterInList(): static { $this->setTypeParam('remove-filter-in-list', true); return $this; }
    public function hasRemoveFilterInList(): bool { return (bool) $this->getTypeParam('remove-filter-in-list', false); }
    public function changeStatusInList(): static { $this->setTypeParam('change-status-in-list', true); return $this; }
    public function hasChangeStatusInList(): bool { return (bool) $this->getTypeParam('change-status-in-list', false); }
}
