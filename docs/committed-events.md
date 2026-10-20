# Committed business outcomes

Classes are registered by the package provider alongside legacy events. Keys ending in `.committed` are safe automation outcomes; earlier event signatures, payloads and timing are preserved. Only scalar identifiers are exposed.

| Event class | Key | Payload properties |
| --- | --- | --- |
| `JobMetric\Taxonomy\Events\Taxonomy\TaxonomyStoreCommittedEvent` | `taxonomy.taxonomy.stored.committed` | `id: int|string` |
| `JobMetric\Taxonomy\Events\Taxonomy\TaxonomyUpdateCommittedEvent` | `taxonomy.taxonomy.updated.committed` | `id: int|string` |
| `JobMetric\Taxonomy\Events\Taxonomy\TaxonomyDeleteCommittedEvent` | `taxonomy.taxonomy.deleted.committed` | `id: int|string` |
| `JobMetric\Taxonomy\Events\Taxonomy\TaxonomyMoveCommittedEvent` | `taxonomy.taxonomy.moved.committed` | `id: int|string` |
| `JobMetric\Taxonomy\Events\TaxonomyRelation\TaxonomyRelationAttachCommittedEvent` | `taxonomy.taxonomy_relation.attached.committed` | `ownerType: string`, `ownerId: int|string`, `taxonomyId: int` |
| `JobMetric\Taxonomy\Events\TaxonomyRelation\TaxonomyRelationDetachCommittedEvent` | `taxonomy.taxonomy_relation.detached.committed` | `ownerType: string`, `ownerId: int|string`, `taxonomyId: int` |
| `JobMetric\Taxonomy\Events\Taxonomy\TaxonomyMergeCommittedEvent` | `taxonomy.taxonomy.merged.committed` | `sourceId: int`, `targetId: int` |

Schedule from the owning operation after successful persistence. Delivery waits for the owning connection outer commit and is discarded on rollback. No-op operations emit nothing. Native Laravel listeners are isolated individually; failures are logged with event key and exception class only. No models, raw inputs, credentials or translated content are included. This is synchronous delivery without durable outbox guarantees.
