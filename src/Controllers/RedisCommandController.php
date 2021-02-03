<?php

namespace OlegKravets\LaravelRedisService\Controllers;

use Illuminate\Support\Collection;

class RedisCommandController
{
    /**
     * Assign model as reserved method detection class for commands
     * @var string
     */
    protected static $bind_model = NULL;

    /**
     * Example of query: users:1 request|where|email|admin@evnedev.com
     * @param $channel
     * @param $message
     * @return array|mixed|string
     * @throws \Exception
     */
    public static function request_parser($channel, $message) : ?Collection
    {
        $packet_rules = explode("||", $message);

        if(count($packet_rules)<2){
            throw new \Exception("Command has not valid format");
        }

        $request_type = $packet_rules[0];
        $method_name = $packet_rules[1];
        $response = null;
        unset($packet_rules[0], $packet_rules[1]);

        if($request_type === "response")
            return NULL;


        // TODO: Defining different routes within of controller classes
        if(method_exists(static::class, $method_name)){ // Search method in current controller
            // Call current controller method with present args
            if(count($packet_rules) === 1){
                $response = call_user_func([static::class, $method_name], array_values($packet_rules)[0]);
            }else
                $response = call_user_func([static::class, $method_name], ...array_values($packet_rules));
        }else{ // If controller doesnt have method that we need - search method in bound model
            $is_nested = false;
            // If request payload contain "[['id','=',1]]" that's mean that this is nested request, that must be deserialized
            if(str_starts_with(array_values($packet_rules)[0], "[[") and
                str_ends_with(array_values($packet_rules)[0], "]]")){
                // Dejsoning nested req payload
                $packet_rules = json_decode(array_values($packet_rules)[0]);
                // Rebinding model as common variable
                $model = static::$bind_model;
                // Whering and getting bound model's method as a nested request
                return new Collection($model::where($packet_rules)->get());
            }
            if((str_starts_with(array_values($packet_rules)[0], "{") and
                    str_ends_with(array_values($packet_rules)[0], "}"))
                or
                (str_starts_with(array_values($packet_rules)[0], "[{") and
                    str_ends_with(array_values($packet_rules)[0], "}]"))
            ){
                $packet_rules = json_decode(array_values($packet_rules)[0], true);
            }

            // Call bound model's method with prepared args
            $response = call_user_func_array([static::$bind_model, $method_name], array_values($packet_rules));

            // Somethimes client is able to retrieve for ex. by method 'find', 'avg', 'count' that retrieve
            // data inside of method, but sometimes here can be 'where' method that just prepares request, and then
            // you should to retrieve data manually
            if(is_object($response) and get_class($response) === "Illuminate\Database\Eloquent\Builder"){
                $response = $response->get();
            }
        }

        return empty($response) ? new Collection() : new Collection($response);
    }

}
