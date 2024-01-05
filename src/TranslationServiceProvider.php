<?php

namespace JobMetric\Translation;

use JobMetric\Metadata\MetadataServiceProvider;
use JobMetric\PackageCore\Exceptions\DependencyPublishableClassNotFoundException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;

class TranslationServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @throws MigrationFolderNotFoundException
     * @throws RegisterClassTypeNotFoundException
     * @throws DependencyPublishableClassNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-translation')
            ->hasConfig()
            ->hasTranslation()
            ->hasMigration()
            ->registerDependencyPublishable(MetadataServiceProvider::class)
            ->registerClass('Translation', Translation::class);
    }
}
