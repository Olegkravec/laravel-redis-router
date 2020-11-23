<?php

namespace OlegKravets\LaravelRedisService\Commands;

use OlegKravets\LaravelRedisService\ServiceHandlers\InboundStream;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use OlegKravets\LaravelRedisService\ServiceHandlers\OutboundStream;
use RedisClient\Exception\InvalidArgumentException;

class ListenServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listen:service {service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws InvalidArgumentException
     */
    public function handle()
    {

        $SERVICE_NAME = $this->argument("service");
        $in_stream = new InboundStream();
        $out_stream = null; // Connect only if inbound stream was successfully connected

        /**
         * Pass out_stream only by &pointer
         */
        $in_stream->subscribe("$SERVICE_NAME:*", static function($type, $pattern, $channel, $message) use (&$out_stream) {
            if ($type === 'psubscribe')
                $out_stream = new OutboundStream();
            elseif ($type === 'pmessage') {
                if ($message === 'quit') // Reservation force quit from chats
                    return false;

                $packet_rules = explode("||", $message);
                $request_type = $packet_rules[0];

                if($request_type === "response")
                    return true;

                $channel_rules = explode(":", $channel);
                $channel_name = $channel_rules[0];
                $packet_id = $channel_rules[1];
                $controller = "App\Http\Controllers\\" . ucwords($channel_name) . "RedisController";


                try {
                    $response = $controller::request_parser($channel, $message);

                    if($response instanceof Collection){
                        $out_stream->response($channel, $response);
                    }
                }catch (\Exception $e){
                    $out_stream->response($channel,
                        [
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'request' => $message
                        ]);
                }
            }
            return true;
        });
        return 0;
    }
}
