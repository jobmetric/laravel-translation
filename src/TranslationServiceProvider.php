<?php

namespace JobMetric\Translation;

use Illuminate\Support\Facades\Blade;
use JobMetric\Metadata\MetadataServiceProvider;
use JobMetric\PackageCore\Exceptions\AssetFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\DependencyPublishableClassNotFoundException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\ViewFolderNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;
use JobMetric\Translation\View\Components\TranslationCard;

class TranslationServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @throws MigrationFolderNotFoundException
     * @throws DependencyPublishableClassNotFoundException
     * @throws ViewFolderNotFoundException
     * @throws AssetFolderNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-translation')
            ->hasConfig()
            ->hasView()
            ->hasAsset()
            ->hasTranslation()
            ->hasMigration()
            ->registerDependencyPublishable(MetadataServiceProvider::class);
    }

    /**
     * After Boot Package
     *
     * @return void
     */
    public function afterBootPackage(): void
    {
        // add alias for components
        Blade::component(TranslationCard::class, 'translation-card');
    }
}
