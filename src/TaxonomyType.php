<?php

namespace JobMetric\Taxonomy;

use Illuminate\Support\Traits\Macroable;
use JobMetric\Media\MediaServiceType;
use JobMetric\Metadata\MetadataServiceType;
use JobMetric\PackageCore\Services\BaseServiceType;
use JobMetric\PackageCore\Services\HierarchicalServiceType;
use JobMetric\PackageCore\Services\InformationServiceType;
use JobMetric\PackageCore\Services\ListChangeStatusServiceType;
use JobMetric\PackageCore\Services\ListExportServiceType;
use JobMetric\PackageCore\Services\ListFilterServiceType;
use JobMetric\PackageCore\Services\ListImportServiceType;
use JobMetric\PackageCore\Services\ListShowDescriptionServiceType;
use JobMetric\PackageCore\Services\ServiceType;
use JobMetric\Translation\TranslationServiceType;
use JobMetric\Url\UrlServiceType;

class TaxonomyType extends ServiceType
{
    use Macroable,
        BaseServiceType,
        InformationServiceType,
        TranslationServiceType,
        MetadataServiceType,
        MediaServiceType,
        HierarchicalServiceType,
        UrlServiceType,
        ListShowDescriptionServiceType,
        ListFilterServiceType,
        ListChangeStatusServiceType,
        ListImportServiceType,
        ListExportServiceType;

    protected function serviceType(): string
    {
        return 'TaxonomyServiceType';
    }
}
