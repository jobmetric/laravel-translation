<?php

namespace JobMetric\Translation;

use Illuminate\Contracts\Container\BindingResolutionException;
use JobMetric\EventSystem\Support\EventRegistry;
use JobMetric\Metadata\MetadataServiceProvider;
use JobMetric\PackageCore\Exceptions\DependencyPublishableClassNotFoundException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;

class TranslationServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @throws MigrationFolderNotFoundException
     * @throws DependencyPublishableClassNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-translation')
            ->hasConfig()
            ->hasTranslation()
            ->hasMigration()
            ->registerDependencyPublishable(MetadataServiceProvider::class);
    }

    /**
     * after boot package
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function afterBootPackage(): void
    {
        // Register events if EventRegistry is available
        // This ensures EventRegistry is available if EventSystemServiceProvider is loaded
        if ($this->app->bound('EventRegistry')) {
            /** @var EventRegistry $registry */
            $registry = $this->app->make('EventRegistry');

            // Translation Events
            $registry->register(\JobMetric\Translation\Events\TranslationStoredEvent::class);
            $registry->register(\JobMetric\Translation\Events\TranslationForgetEvent::class);
        }
    }
}
