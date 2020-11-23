<?php


namespace OlegKravets\LaravelRedisService\ServiceHandlers;

use RedisClient\Exception\InvalidArgumentException;
use RedisClient\RedisClient;

class InboundStream
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
     * @return RedisClient
     */
    public function getConnection(): RedisClient
    {
        return $this->connection;
    }

    /**
     * @param string $p_channel
     * @param callable|null $callback
     * @throws InvalidArgumentException
     */
    public function subscribe(string $p_channel = "default", callable $callback = null): void
    {
        $this->p_channel = $p_channel;
        if(!empty($callback)){
            $this->callback = $callback;
        }else{
            $this->callback = static function($type, $pattern, $channel, $message) {
                if ($type === 'psubscribe')
                    echo 'Subscribed to channel <', $pattern, '>', PHP_EOL;
                elseif ($type === 'pmessage') {
                    echo 'Message <', $message, '> from channel <', $channel, '> by pattern <', $pattern, '>', PHP_EOL;
                    if ($message === 'quit')
                        return false;
                }
                return true;
            };
        }
        $this->connection->psubscribe($this->p_channel, $this->callback);
    }
}
