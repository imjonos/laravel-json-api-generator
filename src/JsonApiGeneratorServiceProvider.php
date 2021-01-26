<?php

namespace Nos\JsonApiGenerator;

use Illuminate\Support\ServiceProvider;
use Nos\JsonApiGenerator\Console\Commands\GenerateJsonApi;

class JsonApiGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'jsonApi');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'nos');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
            $this->commands([
                GenerateJsonApi::class
            ]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jsonapigenerator.php', 'jsonapigenerator');

        // Register the service the package provides.
        $this->app->singleton('jsonapigenerator', function ($app) {
            return new JsonApiGenerator;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['jsonapigenerator'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/jsonapigenerator.php' => config_path('jsonapigenerator.php'),
        ], 'jsonapigenerator.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/nos'),
        ], 'jsonapigenerator.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/nos'),
        ], 'jsonapigenerator.views');*/

        // Publishing the translation files.
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/nos'),
        ], 'jsonapigenerator.langs');

        // Registering package commands.
        // $this->commands([]);
    }
}
