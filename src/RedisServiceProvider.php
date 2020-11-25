<?php
namespace OlegKravets\LaravelRedisService;

use Illuminate\Support\ServiceProvider;
use OlegKravets\LaravelRedisService;
use OlegKravets\LaravelRedisService\Console\ListenServiceCommand;

class RedisServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ListenServiceCommand::class
            ]);
        }
    }
}
