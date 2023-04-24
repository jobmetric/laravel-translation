<?php

namespace JobMetric\Translation\Providers;

use Cache;
use Illuminate\Support\ServiceProvider;
use JobMetric\Translation\Console\Commands\CreateViewCommand;
use JobMetric\Translation\TranslationService;
use JobMetric\Translation\Http\Middleware\SetConfig;
use JobMetric\Translation\Models\Setting;
use JobMetric\Translation\Object\Config;
use Illuminate\Support\Facades\Schema;

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
    public function boot()
    {
        $this->registerPublishables();
    }

    /**
     * Register publishables
     *
     * @return void
     */
    private function registerPublishables(): void
    {
        // publish config
        $this->publishes([
            realpath(__DIR__.'/../../config/config.php') => config_path('translation.php')
        ], 'config');

        // publish migration
        if (!$this->migrationExists('create_translations_table')) {
            $this->publishes([
                realpath(__DIR__.'/../../database/migrations/create_translations_table.php.stub') => database_path('migrations/'.date('Y_m_d_His', time()).'_create_translations_table.php')
            ], 'migrations');
        }
    }

    private function migrationExists($migration): bool
    {
        $path = database_path('migrations/');
        $files = scandir($path);

        foreach ($files as &$value) {
            $position = strpos($value, $migration);
            if ($position !== false) {
                return true;
            }
        }

        return false;
    }
}
