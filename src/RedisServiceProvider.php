<?php

use Illuminate\Support\ServiceProvider;
use OlegKravets\LaravelRedisService;

class RedisServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'artogrig');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'artogrig');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {

            // Publishing the configuration file.
            $this->publishes([
                __DIR__.'/../app/Console/Commands/ListenServiceCommand.php' => app_path('Console/Commands/ListenServiceCommand.php'),
            ], 'laravelredisservice.command');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/artogrig'),
            ], 'laravelmakeservice.views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/artogrig'),
            ], 'laravelmakeservice.views');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/artogrig'),
            ], 'laravelmakeservice.views');*/

            // Registering package commands.
            // $this->commands([]);

            $this->commands([
                LaravelRedisService\Commands\ListenServiceCommand::class
            ]);
        }
    }
}