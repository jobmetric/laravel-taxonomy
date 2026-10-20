<?php

namespace JobMetric\Taxonomy\Events\Taxonomy;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

/**
 * Describe a committed Taxonomy merged outcome using allowlisted identifiers.
 */
readonly class TaxonomyMergeCommittedEvent implements DomainEvent
{
    /**
     * Capture identifiers without models, input values or credentials.
     *
     * @param int $sourceId
     * @param int $targetId
     *
     * @return void
     */
    public function __construct(
        public int $sourceId,
        public int $targetId
    ) {
    }

    /**
     * Return the stable key used by automation consumers.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'taxonomy.taxonomy.merged.committed';
    }

    /**
     * Describe the outcome for translated discovery interfaces.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'taxonomy::automation.taxonomy_merged.group', 'taxonomy::automation.taxonomy_merged.title', 'taxonomy::automation.taxonomy_merged.description', 'fas fa-bolt', ['automation', 'committed', 'taxonomy']);
    }
}
