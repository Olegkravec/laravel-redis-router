<?php


namespace OlegKravets\LaravelRedisService\ServiceHandlers;


use OlegKravets\LaravelRedisService\Controllers\RedisCommandController;
use Illuminate\Support\Str;
use RedisClient\Exception\InvalidArgumentException;
use RedisClient\RedisClient;

class OutboundStream
{
    private $connection;
    private $p_channel = NULL;
    private $callback = NULL;

    /**
     * InboundStream constructor.
     * @param int $timeout
     */
    public function __construct(int $timeout = 1)
    {
        $this->connection = new RedisClient([
            'timeout' => $timeout,
            'server' => env('REDIS_HOST').':' . env('REDIS_PORT'),
            'password' => env('REDIS_PASSWORD')
        ]);
    }

    /**
     * Request some data from another redis handled services
     *
     * @param string $req_channel
     * @param callable $callback
     * @param string $req_method
     * @param string $req_message
     * @throws InvalidArgumentException
     */
    public function request(string $req_channel, callable $callback, string $req_method, string $req_message){
        $response_handler = new InboundStream(0);
        $packet_id = Str::uuid();
        $out_stream = $this->connection;
        if(strpos($req_channel, "*") !== false){
            $req_channel = str_replace("*", $packet_id, $req_channel);
        }
        $time = microtime(true);
        $response_handler->subscribe($req_channel,
            static function($type, $pattern, $channel, $message) use
                ($req_channel, $callback, $req_message, $req_method, $out_stream) {

            if ($type === 'psubscribe'){
                /**
                 * Publishing our request once we have been joined to channel
                 */
                $out_stream->publish($req_channel, "request||$req_method||$req_message");
            }
            elseif ($type === 'pmessage') {
                if ($message === 'quit')
                    return false;

                /**
                 * Calling our callback with no-transform response model
                 */
                $response = explode("||", $message,2);
                if($response[0] === "response" and $req_channel === $channel){
                    $callback($response[1]);
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Uses only for returning from redis handled service to request-service
     * @param string $channel
     * @param $message
     * @throws \JsonException
     */
    public function response(string $channel, $message){
        $this->connection->publish($channel, "response||".json_encode($message, JSON_THROW_ON_ERROR));
    }
}
