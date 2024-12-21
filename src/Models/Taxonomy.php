<?php

namespace JobMetric\Taxonomy\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JobMetric\Comment\Contracts\CommentContract;
use JobMetric\Comment\HasComment;
use JobMetric\Layout\Contracts\LayoutContract;
use JobMetric\Layout\HasLayout;
use JobMetric\Like\HasLike;
use JobMetric\Media\Contracts\MediaContract;
use JobMetric\Media\HasDynamicFile;
use JobMetric\Media\HasFile;
use JobMetric\Membership\Contracts\MemberContract;
use JobMetric\Membership\HasMember;
use JobMetric\Metadata\Contracts\MetaContract;
use JobMetric\Metadata\HasDynamicMeta;
use JobMetric\Metadata\HasMeta;
use JobMetric\PackageCore\Models\HasBooleanStatus;
use JobMetric\Star\HasStar;
use JobMetric\Taxonomy\Events\TaxonomyAllowMemberCollectionEvent;
use JobMetric\Translation\Contracts\TranslationContract;
use JobMetric\Translation\HasDynamicTranslation;
use JobMetric\Translation\HasTranslation;
use JobMetric\Url\HasUrl;

/**
 * JobMetric\Taxonomy\Models\Taxonomy
 *
 * @property int $id
 * @property string $type
 * @property int $parent_id
 * @property int $ordering
 * @property bool $status
 *
 * @method static find(int $int)
 */
class Taxonomy extends Model implements TranslationContract, MetaContract, MediaContract, CommentContract, MemberContract, LayoutContract
{
    use HasFactory,
        HasBooleanStatus,
        HasTranslation,
        HasDynamicTranslation,
        HasMeta,
        HasDynamicMeta,
        HasFile,
        HasDynamicFile,
        HasComment,
        HasMember,
        HasLike,
        HasStar,
        HasLayout,
        HasUrl;

    protected $fillable = [
        'parent_id',
        'type',
        'ordering',
        'status',
        'translation'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => 'string',
        'parent_id' => 'integer',
        'ordering' => 'integer',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getTable()
    {
        return config('taxonomy.tables.taxonomy', parent::getTable());
    }

    /**
     * Check if a comment for a specific model needs to be approved.
     *
     * @return bool
     */
    public function needsCommentApproval(): bool
    {
        return true;
    }

    /**
     * allow the member collection.
     *
     * @return array
     */
    public function allowMemberCollection(): array
    {
        $event = new TaxonomyAllowMemberCollectionEvent([
            'owner' => 'single',
        ]);

        event($event);

        return $event->allowMemberCollection;
    }

    /**
     * Layout page type.
     *
     * @return string
     */
    public function layoutPageType(): string
    {
        return 'taxonomy';
    }

    /**
     * Layout collection field.
     *
     * @return string|null
     */
    public function layoutCollectionField(): ?string
    {
        return null;
    }

    /**
     * Get the taxonomy relations.
     *
     * @return HasMany
     */
    public function taxonomyRelations(): HasMany
    {
        return $this->hasMany(TaxonomyRelation::class, 'taxonomy_id', 'id');
    }

    /**
     * Get the paths of the taxonomy.
     *
     * @return HasMany
     */
    public function paths(): HasMany
    {
        return $this->hasMany(TaxonomyPath::class, 'taxonomy_id');
    }

    /**
     * Get the children taxonomy.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Scope a query to only include taxonomies of a given type.
     *
     * @param Builder $query
     * @param string $type
     *
     * @return Builder
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
