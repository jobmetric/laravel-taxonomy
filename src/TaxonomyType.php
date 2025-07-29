<?php

namespace JobMetric\Taxonomy;

use Illuminate\Support\Traits\Macroable;
use JobMetric\Media\Typeify\HasMediaType;
use JobMetric\Metadata\Typeify\HasMetadataType;
use JobMetric\Translation\Typeify\HasTranslationType;
use JobMetric\Typeify\BaseType;
use JobMetric\Typeify\Traits\HasHierarchicalType;
use JobMetric\Typeify\Traits\HasListOptionType;
use JobMetric\Typeify\Traits\HasImportType;
use JobMetric\Typeify\Traits\HasExportType;
use JobMetric\Url\Typeify\HasUrlType;

class TaxonomyType extends BaseType
{
    use Macroable,
        HasHierarchicalType,
        HasTranslationType,
        HasMetadataType,
        HasMediaType,
        HasUrlType,
        HasListOptionType,
        HasImportType,
        HasExportType;

    protected function typeName(): string
    {
        return 'taxonomy-type';
    }
}
