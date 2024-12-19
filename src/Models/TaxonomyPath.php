<?php

namespace JobMetric\Taxonomy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * JobMetric\Taxonomy\Models\TaxonomyPath
 *
 * @property int $type
 * @property int $taxonomy_id
 * @property int $path_id
 * @property int $level
 */
class TaxonomyPath extends Pivot
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'type',
        'taxonomy_id',
        'path_id',
        'level'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => 'string',
        'taxonomy_id' => 'integer',
        'path_id' => 'integer',
        'level' => 'integer'
    ];

    public function getTable()
    {
        return config('taxonomy.tables.taxonomy_path', parent::getTable());
    }

    /**
     * Get the taxonomy that owns the path.
     *
     * @return BelongsTo
     */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id');
    }

    /**
     * Get the path that owns the taxonomy.
     *
     * @return BelongsTo
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class, 'path_id');
    }
}
