<?php

namespace JobMetric\Taxonomy\Events\Taxonomy;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

/**
 * Describe a committed Taxonomy moved outcome using allowlisted identifiers.
 */
readonly class TaxonomyMoveCommittedEvent implements DomainEvent
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
        return 'taxonomy.taxonomy.moved.committed';
    }

    /**
     * Describe the outcome for translated discovery interfaces.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'taxonomy::automation.taxonomy_moved.group', 'taxonomy::automation.taxonomy_moved.title', 'taxonomy::automation.taxonomy_moved.description', 'fas fa-bolt', ['automation', 'committed', 'taxonomy']);
    }
}
