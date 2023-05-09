<?php

namespace JobMetric\Translation\Providers;

use Illuminate\Support\ServiceProvider;
use JobMetric\Metadata\Providers\MetadataServiceProvider;
use JobMetric\Translation\TranslationService;

class TranslationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('TranslationService', function ($app) {
            return new TranslationService($app);
        });

        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'translation');
    }

    /**
     * boot provider
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerMigrations();
        $this->registerPublishables();

        // set translations
        $this->loadTranslationsFrom(realpath(__DIR__.'/../../lang'), 'translation');
    }

    /**
     * Register the Passport migration files.
     *
     * @return void
     */
    protected function registerMigrations(): void
    {
        if($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
    }

    /**
     * register publishables
     *
     * @return void
     */
    protected function registerPublishables(): void
    {
        if($this->app->runningInConsole()) {
            // run dependency publishable
            $this->publishes(self::pathsToPublish(MetadataServiceProvider::class), 'metadata');

            // publish config
            $this->publishes([
                realpath(__DIR__.'/../../config/config.php') => config_path('translation.php')
            ], 'translation-config');

            // publish migration
            $this->publishes([
                realpath(__DIR__.'/../../database/migrations') => database_path('migrations')
            ], 'translation-migrations');
        }
    }
}
