<?php

namespace JobMetric\Taxonomy;

use Illuminate\Support\Facades\Route;
use JobMetric\PackageCore\Exceptions\AssetFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\Exceptions\ViewFolderNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;
use JobMetric\Taxonomy\Models\Taxonomy as TaxonomyModel;
use JobMetric\Taxonomy\Models\TaxonomyPath;
use JobMetric\Taxonomy\Models\TaxonomyRelation;

class TaxonomyServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @param PackageCore $package
     *
     * @return void
     * @throws MigrationFolderNotFoundException
     * @throws RegisterClassTypeNotFoundException
     * @throws ViewFolderNotFoundException
     * @throws AssetFolderNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-taxonomy')
            ->hasConfig()
            ->hasMigration()
            ->hasTranslation()
            ->hasAsset()
            ->hasRoute()
            ->hasView()
            ->registerClass('Taxonomy', Taxonomy::class)
            ->registerClass('TaxonomyType', TaxonomyType::class);
    }

    /**
     * After register package
     *
     * @return void
     */
    public function afterRegisterPackage(): void
    {
        // Register model binding
        Route::model('jm_taxonomy', TaxonomyModel::class);
        Route::model('jm_taxonomy_path', TaxonomyPath::class);
        Route::model('jm_taxonomy_relation', TaxonomyRelation::class);
    }
}
