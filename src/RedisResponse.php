<?php


namespace OlegKravets\LaravelRedisService;


use Illuminate\Support\Collection;

class RedisResponse
{
    public static function ok(string $message = "Handled.") : Collection {
        return new Collection(["status" => true, "message" => $message]);
    }
    public static function success($data = [], $message = "Successfully handled"){
        return new Collection(["status" => true, "message" => $message, "data" => $data]);
    }
    public static function fail(string $message = "Handled.") : Collection {
        return new Collection(["status" => false, "message" => $message]);
    }
}
