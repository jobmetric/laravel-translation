<?php

namespace JobMetric\Translation\Providers;

use Illuminate\Support\ServiceProvider;
use JobMetric\Translation\TranslationService;

class TranslationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('TranslationService', function ($app) {
            return new TranslationService;
        });
    }

    /**
     * boot provider
     *
     * @return void
     */
    public function boot(): void
    {
        // publish config
        $this->publishes([
            realpath(__DIR__.'/../../config/config.php') => config_path('translation.php')
        ], 'config');

        // publish migration
        if (!$this->migrationTranslationExists()) {
            $this->publishes([
                realpath(__DIR__.'/../../database/migrations/create_translations_table.php.stub') => database_path('migrations/'.date('Y_m_d_His', time()).'_create_translations_table.php')
            ], 'migrations');
        }
    }

    /**
     * check migration translation table
     *
     * @return bool
     */
    private function migrationTranslationExists(): bool
    {
        $path = database_path('migrations/');
        $files = scandir($path);

        foreach ($files as &$value) {
            $position = strpos($value, 'create_translations_table');
            if ($position !== false) {
                return true;
            }
        }

        return false;
    }
}
