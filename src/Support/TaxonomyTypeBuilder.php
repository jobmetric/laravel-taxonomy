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
