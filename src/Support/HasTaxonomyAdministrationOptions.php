<?php

namespace JobMetric\Taxonomy\Support;

use InvalidArgumentException;

/** Fluent options used by taxonomy administration screens. */
trait HasTaxonomyAdministrationOptions
{
    public function viewPermission(string $permission): static
    {
        $this->assertPermission($permission);
        $this->setTypeParam('view-permission', $permission);

        return $this;
    }

    public function getViewPermission(): ?string
    {
        return $this->getTypeParam('view-permission')
            ?? ($this->getPermission() === null ? null : $this->getPermission().'.view');
    }

    public function managePermission(string $permission): static
    {
        $this->assertPermission($permission);
        $this->setTypeParam('manage-permission', $permission);

        return $this;
    }

    public function getManagePermission(): ?string
    {
        return $this->getTypeParam('manage-permission')
            ?? ($this->getPermission() === null ? null : $this->getPermission().'.manage');
    }

    public function unlockPermission(string $permission): static
    {
        $this->assertPermission($permission);
        $this->setTypeParam('unlock-permission', $permission);

        return $this;
    }

    public function getUnlockPermission(): ?string
    {
        return $this->getTypeParam('unlock-permission');
    }

    public function optimisticLocking(bool $enabled = true): static
    {
        $this->setTypeParam('optimistic-locking', $enabled);

        return $this;
    }

    public function hasOptimisticLocking(): bool
    {
        return (bool) $this->getTypeParam('optimistic-locking', false);
    }

    /** Mark this taxonomy type as having a help panel in its list page. */
    public function help(): static
    {
        $this->setTypeParam('has-help', true);

        return $this;
    }

    public function hasHelp(): bool
    {
        return (bool) $this->getTypeParam('has-help', false);
    }

    /** Append one row action, preserving actions registered by other extensions. */
    public function rowAction(array $action): static
    {
        $this->rowActions([$action]);

        return $this;
    }

    /** Append row actions; each action needs a key, label, icon, and URL. */
    public function rowActions(array $actions): static
    {
        foreach ($actions as $action) {
            $this->assertRowAction($action);
        }

        $this->appendTypeParam('row-actions', array_values($actions));

        return $this;
    }

    public function getRowActions(): array
    {
        return $this->getTypeParam('row-actions', []);
    }

    public function backUrl(string $url): static
    {
        if (trim($url) === '') {
            throw new InvalidArgumentException('A non-empty taxonomy back URL is required.');
        }
        $this->setTypeParam('back-url', $url);

        return $this;
    }

    public function getBackUrl(): ?string
    {
        return $this->getTypeParam('back-url');
    }

    protected function assertPermission(string $permission): void
    {
        if (!preg_match('/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)*$/', $permission)) {
            throw new InvalidArgumentException('Invalid taxonomy administration permission.');
        }
    }

    protected function assertRowAction(mixed $action): void
    {
        if (!is_array($action)
            || !isset($action['key'], $action['label'], $action['icon'], $action['url'])
            || !is_string($action['key']) || trim($action['key']) === ''
            || !is_string($action['label']) || trim($action['label']) === ''
            || !is_string($action['icon']) || trim($action['icon']) === ''
            || !is_string($action['url']) || trim($action['url']) === '') {
            throw new InvalidArgumentException('A taxonomy row action requires non-empty key, label, icon, and URL values.');
        }
    }
}
