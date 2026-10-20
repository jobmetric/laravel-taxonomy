<?php

namespace JobMetric\Taxonomy\Events\TaxonomyRelation;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

/**
 * Describe a committed TaxonomyRelation attached outcome using allowlisted identifiers.
 */
readonly class TaxonomyRelationAttachCommittedEvent implements DomainEvent
{
    /**
     * Capture identifiers without models, input values or credentials.
     *
     * @param string $ownerType
     * @param int|string $ownerId
     * @param int $taxonomyId
     *
     * @return void
     */
    public function __construct(
        public string $ownerType,
        public int|string $ownerId,
        public int $taxonomyId
    ) {
    }

    /**
     * Return the stable key used by automation consumers.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'taxonomy.taxonomy_relation.attached.committed';
    }

    /**
     * Describe the outcome for translated discovery interfaces.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'taxonomy::automation.taxonomy_relation_attached.group', 'taxonomy::automation.taxonomy_relation_attached.title', 'taxonomy::automation.taxonomy_relation_attached.description', 'fas fa-bolt', ['automation', 'committed', 'taxonomy']);
    }
}
