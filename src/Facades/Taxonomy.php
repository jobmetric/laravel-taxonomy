<?php

namespace JobMetric\Taxonomy\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Taxonomy\Taxonomy
 *
 * @method static \Spatie\QueryBuilder\QueryBuilder query(string $type, array $filter = [], array $with = [])
 * @method static \Illuminate\Http\Resources\Json\AnonymousResourceCollection paginate(string $type, array $filter = [], int $page_limit = 15, array $with = [])
 * @method static \Illuminate\Http\Resources\Json\AnonymousResourceCollection all(string $type, array $filter = [], array $with = [])
 * @method static array store(array $data)
 * @method static array update(int $taxonomy_id, array $data)
 * @method static array delete(int $taxonomy_id)
 * @method static string getName(int $taxonomy_id, bool $concat = true, ?string $locale = null)
 * @method static array usedIn(int $taxonomy_id)
 * @method static bool hasUsed(int $taxonomy_id)
 * @method static array setTranslation(array $data)
 */
class Taxonomy extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \JobMetric\Taxonomy\Taxonomy::class;
    }
}
