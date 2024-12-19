<?php

namespace JobMetric\Taxonomy\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use JobMetric\Taxonomy\Events\TaxonomizableResourceEvent;

/**
 * JobMetric\Taxonomy\Models\TaxonomyRelation
 *
 * @property int $relatable_type
 * @property int $relatable_id
 * @property int $taxonomy_id
 * @property int $collection
 *
 * @property Taxonomy $taxonomy
 * @property mixed $taxonomizable
 * @property mixed $taxonomizable_resource
 */
class TaxonomyRelation extends Pivot
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'taxonomy_id',
        'taxonomizable_type',
        'taxonomizable_id',
        'collection'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'taxonomy_id' => 'integer',
        'taxonomizable_type' => 'string',
        'taxonomizable_id' => 'integer',
        'collection' => 'string'
    ];

    public function getTable()
    {
        return config('taxonomy.tables.taxonomy_relation', parent::getTable());
    }

    /**
     * Get the taxonomy that owns the relation.
     *
     * @return BelongsTo
     */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }

    /**
     * Get the taxonomizable model that owns the taxonomy.
     *
     * @return MorphTo
     */
    public function taxonomizable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include taxonomies of a given type.
     *
     * @param Builder $query
     * @param string $collection
     * @return Builder
     */
    public function scopeByCollection(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }

    /**
     * Get the taxonomizable resource attribute.
     */
    public function getTaxonomizableResourceAttribute()
    {
        $event = new TaxonomizableResourceEvent($this->taxonomizable);
        event($event);

        return $event->resource;
    }
}
