<?php


use Illuminate\Support\ServiceProvider;
use OlegKravets\LaravelRedisService;

class RedisServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            LaravelRedisService\Console\ListenServiceCommand::class
        ]);
    }
}
