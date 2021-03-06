<?php

namespace CodeZero\Translator;

use CodeZero\Translator\Exporter\Exporter;
use CodeZero\Translator\Exporter\FileExporter;
use CodeZero\Translator\FileLoader\FileLoader;
use CodeZero\Translator\FileLoader\LaravelFileLoader;
use CodeZero\Translator\Importer\DatabaseImporter;
use CodeZero\Translator\Importer\Importer;
use CodeZero\Translator\Validators\UniqueTranslationKey;
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
        $this->registerValidators();
        $this->loadMigrations();
        $this->registerPublishableFiles();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
        $this->bindClasses();
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
     * Register custom validators.
     *
     * @return void
     */
    protected function registerValidators()
    {
        Validator::extend('unique_translation_key', UniqueTranslationKey::class.'@validate');
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
     * Merge published configuration file with
     * the original package configuration file.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(__DIR__."/../config/{$this->name}.php", $this->name);
    }

    /**
     * Bind classes in the IoC container.
     *
     * @return void
     */
    protected function bindClasses()
    {
        $this->app->bind(FileLoader::class, LaravelFileLoader::class);
        $this->app->bind(Importer::class, DatabaseImporter::class);
        $this->app->bind(Exporter::class, FileExporter::class);
    }
}
