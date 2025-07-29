<?php

namespace JobMetric\Taxonomy\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \JobMetric\Taxonomy\TaxonomyType
 *
 * @method static \JobMetric\Taxonomy\TaxonomyType define(string $type)
 * @method static \JobMetric\Taxonomy\TaxonomyType type(string $type)
 * @method static array get()
 * @method static array getTypes()
 * @method static bool hasType(string $type)
 * @method static void ensureTypeExists(string $type)
 */
class TaxonomyType extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'TaxonomyType';
    }
}
