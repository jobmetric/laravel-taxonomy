<?php

namespace JobMetric\Taxonomy\Events\Taxonomy;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

/**
 * Describe a committed Taxonomy deleted outcome using allowlisted identifiers.
 */
readonly class TaxonomyDeleteCommittedEvent implements DomainEvent
{
    /**
     * Capture identifiers without models, input values or credentials.
     *
     * @param int|string $id
     *
     * @return void
     */
    public function __construct(
        public int|string $id
    ) {
    }

    /**
     * Return the stable key used by automation consumers.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'taxonomy.taxonomy.deleted.committed';
    }

    /**
     * Describe the outcome for translated discovery interfaces.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'taxonomy::automation.taxonomy_deleted.group', 'taxonomy::automation.taxonomy_deleted.title', 'taxonomy::automation.taxonomy_deleted.description', 'fas fa-bolt', ['automation', 'committed', 'taxonomy']);
    }
}
