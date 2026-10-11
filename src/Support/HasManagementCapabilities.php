<?php

namespace JobMetric\Taxonomy\Support;

use InvalidArgumentException;

/** Optional capabilities consumed by taxonomy administration clients. */
trait HasManagementCapabilities
{
    public function sortable(bool $enabled = true): static { $this->setTypeParam('sortable', $enabled); return $this; }
    public function hasSortable(): bool { return (bool) $this->getTypeParam('sortable', false); }
    /** Enable the SEO fields and metadata controls for this taxonomy type. */
    public function hasSeo(bool $enabled = true): static
    {
        $wasEnabled = $this->isSeoEnabled();
        $this->setTypeParam('has-seo', $enabled);
        // Keep the legacy option readable for existing integrations.
        $this->setTypeParam('seo', $enabled);

        if ($enabled && ! $wasEnabled) {
            $this->seoTranslation();
            $this->media([['collection' => 'seo_image', 'multiple' => false, 'mimeTypes' => ['image']]]);
        } elseif (! $enabled && $wasEnabled) {
            $translations = $this->getTranslation()
                ->reject(fn ($field): bool => in_array(
                    (string) data_get($field->customField->params, 'uniqName'),
                    ['meta_title', 'meta_description', 'meta_keywords'],
                    true
                ))
                ->values()
                ->all();
            $this->setTypeParam('translation', $translations);
            $this->setTypeParam('media', $this->getMedia()
                ->reject(fn ($item): bool => $item->getCollection() === 'seo_image')
                ->values()
                ->all());
        }

        return $this;
    }

    /** Keep the previous fluent name as a compatibility alias. */
    public function seo(bool $enabled = true): static { return $this->hasSeo($enabled); }

    public function isSeoEnabled(): bool
    {
        return (bool) $this->getTypeParam('has-seo', $this->getTypeParam('seo', false));
    }
    public function mergeable(bool $enabled = true): static { $this->setTypeParam('mergeable', $enabled); return $this; }
    public function hasMergeable(): bool { return (bool) $this->getTypeParam('mergeable', false); }
    public function showUsage(bool $enabled = true): static { $this->setTypeParam('show-usage', $enabled); return $this; }
    public function hasShowUsage(): bool { return (bool) $this->getTypeParam('show-usage', false); }
    public function multipleSelection(bool $enabled = true): static { $this->setTypeParam('multiple-selection', $enabled); return $this; }
    public function hasMultipleSelection(): bool { return (bool) $this->getTypeParam('multiple-selection', true); }
    public function leafOnly(bool $enabled = true): static { $this->setTypeParam('leaf-only', $enabled); return $this; }
    public function hasLeafOnly(): bool { return (bool) $this->getTypeParam('leaf-only', false); }

    /** Set the maximum number of levels, counting the root as level one. */
    public function maxDepth(int $depth): static
    {
        if ($depth < 1 || $depth > 100) throw new InvalidArgumentException('Taxonomy depth must be between 1 and 100.');
        $this->setTypeParam('max-depth', $depth);
        return $this;
    }

    public function getMaxDepth(): ?int { return $this->getTypeParam('max-depth'); }

    /** Set the registered administration permission protecting this type. */
    public function permission(string $permission): static
    {
        if (!preg_match('/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)*$/', $permission)) throw new InvalidArgumentException('Invalid taxonomy permission.');
        $this->setTypeParam('permission', $permission);
        return $this;
    }

    public function getPermission(): ?string { return $this->getTypeParam('permission'); }

    /** Register a named frontend route; the client supplies the slug parameter. */
    public function publicRoute(string $route): static
    {
        if (trim($route) === '' || preg_match('/\s/', $route)) {
            throw new InvalidArgumentException('A non-empty named frontend route is required.');
        }
        $this->setTypeParam('public-route', $route);
        return $this;
    }
    public function getPublicRoute(): ?string { return $this->getTypeParam('public-route'); }

    /** Define a validated custom field, stored in the existing metadata table. */
    public function field(string $key, array $definition): static
    {
        if (!preg_match('/^[a-z][a-z0-9_]{0,63}$/', $key)) throw new InvalidArgumentException('Invalid taxonomy field key.');
        $definition += ['label' => $key, 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255'], 'options' => []];
        if (!in_array($definition['type'], ['text', 'textarea', 'number', 'color', 'select', 'checkbox'], true)) throw new InvalidArgumentException('Unsupported taxonomy field control.');
        $fields = $this->getFields();
        $fields[$key] = $definition;
        $this->setTypeParam('custom-fields', $fields);
        return $this;
    }

    public function getFields(): array { return $this->getTypeParam('custom-fields', []); }
}
