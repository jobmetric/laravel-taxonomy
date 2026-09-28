<?php

namespace JobMetric\Taxonomy\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \JobMetric\Taxonomy\Support\TaxonomyTypeBuilder register(string $type, array $options = [])
 * @method static array get(string $type)
 * @method static \JobMetric\Taxonomy\Support\TaxonomyTypeBuilder for(string $type)
 * @method static mixed getOption(string $type, string $key, mixed $default = null)
 * @method static bool has(string $type)
 * @method static void ensureExists(string $type)
 * @method static array all()
 * @method static array values()
 * @method static \JobMetric\Taxonomy\Support\TaxonomyTypeRegistry unregister(string $type)
 * @method static \JobMetric\Taxonomy\Support\TaxonomyTypeRegistry clear()
 */
class TaxonomyTypeRegistry extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'TaxonomyTypeRegistry';
    }
}
