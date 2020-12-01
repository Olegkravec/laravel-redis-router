<?php


namespace OlegKravets\LaravelRedisService;


use Illuminate\Support\Collection;

class RedisResponse
{
    public static function ok(string $message = "Handled.") : Collection {
        return new Collection(["status" => true, "message" => $message]);
    }
    public static function fail(string $message = "Handled.") : Collection {
        return new Collection(["status" => false, "message" => $message]);
    }
}
