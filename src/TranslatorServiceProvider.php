<?php

namespace CodeZero\Translator;

use CodeZero\Translator\Commands\FormatLangFiles;
use CodeZero\Translator\Validators\UniqueTranslationKey;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class TranslatorServiceProvider extends ServiceProvider
{
    /**
     * The package name.
     *
     * @var string
     */
    protected $name = 'translator';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutes();
        $this->loadMigrations();
        $this->registerPublishableFiles();
        $this->registerCommands();
        $this->registerValidators();

        Request::macro('optional', function ($keys) {
            return array_intersect_key($this->all(), array_flip($keys));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Load package routes.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
    }

    /**
     * Load package migrations.
     *
     * @return void
     */
    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register the publishable files.
     *
     * @return void
     */
    protected function registerPublishableFiles()
    {
        $this->publishes([
            __DIR__."/../config/{$this->name}.php" => config_path("{$this->name}.php"),
        ], 'config');
    }

    /**
     * Register the Artisan console commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands([
            FormatLangFiles::class,
        ]);
    }

    /**
     * Register custom validators.
     *
     * @return void
     */
    protected function registerValidators()
    {
        Validator::extend('unique_translation_key', UniqueTranslationKey::class.'@validate');
    }

    /**
     * Merge published configuration file with
     * the original package configuration file.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(__DIR__."/../config/{$this->name}.php", $this->name);
    }
}
