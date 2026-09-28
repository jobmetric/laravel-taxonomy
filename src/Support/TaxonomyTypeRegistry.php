<?php

namespace JobMetric\Taxonomy\Support;

use Illuminate\Support\Arr;
use JobMetric\Taxonomy\Exceptions\TaxonomyTypeNotFoundException;

class TaxonomyTypeRegistry
{
    /** @var array<string, array<string, mixed>> */
    protected array $types = [];

    public function register(string $type, array $options = []): TaxonomyTypeBuilder
    {
        $isNew = ! $this->has($type);
        $this->types[$type] ??= [];

        $builder = new TaxonomyTypeBuilder($type, $this);

        if ($isNew) {
            $builder->bootDefaults();
        }

        return $builder->apply($options);
    }

    public function get(string $type): array
    {
        $this->ensureExists($type);

        return $this->types[$type];
    }

    public function for(string $type): TaxonomyTypeBuilder
    {
        $this->ensureExists($type);

        return new TaxonomyTypeBuilder($type, $this);
    }

    public function getOption(string $type, string $key, mixed $default = null): mixed
    {
        return Arr::get($this->get($type), $key, $default);
    }

    public function setOption(string $type, string $key, mixed $value): void
    {
        $this->ensureExists($type);
        Arr::set($this->types[$type], $key, $value);
    }

    public function mergeOption(string $type, string $key, mixed $value): void
    {
        $current = $this->getOption($type, $key);

        if (is_array($current) && is_array($value) && Arr::isAssoc($current) && Arr::isAssoc($value)) {
            $value = array_replace_recursive($current, $value);
        }

        $this->setOption($type, $key, $value);
    }

    public function appendOption(string $type, string $key, array $values): void
    {
        $current = $this->getOption($type, $key, []);
        $this->setOption($type, $key, array_values(array_merge(is_array($current) ? $current : [], $values)));
    }

    public function has(string $type): bool
    {
        return array_key_exists($type, $this->types);
    }

    public function ensureExists(string $type): void
    {
        if (! $this->has($type)) {
            throw new TaxonomyTypeNotFoundException($type);
        }
    }

    public function all(): array
    {
        return $this->types;
    }

    public function values(): array
    {
        return array_keys($this->types);
    }

    public function unregister(string $type): self
    {
        unset($this->types[$type]);

        return $this;
    }

    public function clear(): self
    {
        $this->types = [];

        return $this;
    }
}
