# laravel-redis-router

## Installing

`composer require olegkravec/laravel-redis-router`

## General

laravel-redis-router is implementation similar ro Laravel MVC scheme for handling and sending Redis Pub/Sub messages

## How it is working?

For example you have 2 different services, called as Service A and Service B, both of services is REST(doesnt metter) API applications, but Service B must get data from Service A.
There are different way how to receive data, for example HTTP API requests, but in highload projects http requests up to version 3 is too slow for transiving data between services, so we need something better.

Redis Pub/Sub mechanism allows subscribe to channel, and publish message to all subscribers, all subscribes will receive data in real time. 

We will make example where Service A will subscribe to channel `users:*` and receive all requests and will send back to channel response.
Service B will requests some specific data(User).

### Step 1 - registering Service A

For lounching laravel-redis-router we should run:
`php artisan listen:service users`

That's mean that Service A will subscribe for {users} channel. 

### Step 2 - service controller for Service A

`php artisan make:controller UserRedisController`

**Patter for creation:** {SERVICE_NAME}RedisController
For example: for notification service we must register ***NotificationRedisController***... eg...




### Step 3 - implementing first method for Service A

```
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Collection;
use OlegKravets\LaravelRedisService\Controllers\RedisCommandController;

class UserRedisController extends RedisCommandController
{
    protected static $bind_model = User::class;

    public static function test(string $data) : Collection {
        return new Collection(['ok!', "key" => $data . " World"]);
    }
}
```

First that you must to know: each controller can be bound to Model(Eloqument), thats allow request data from Service B directly from DB via present model

We implemented `test` method, that has argument `$data`, there is no limits with arguments, you can declare as much as you need, also it can be in any **basic** type.

***Redis controller always must return Collection**

### Step 4 - requesting the data from Service B

#### Requesting controller's method

```
$out = new OutboundStream();
    $out->request("users:*", static function($response) {
        echo "Response is: $response";
    }, "test", "Hello");
```
In your console you will see:
`Response is: {'0':"ok!", "key": "Hello World"}`

Response is `JSON` object with your data.

#### Requesting data directly from DB

```
$out = new OutboundStream();
    $out->request("users:*", static function($response) {
        $user = json_decode($response);
        echo "Response is: " . $user->name;
    }, 'find', 1);
```

In your console you will see:
`Response is: Oleg`

Response is `JSON` object with your data.

### How it looks in Redis Monitor
```
SERVICE A - "PSUBSCRIBE" "users:*"
SERVICE B - "PSUBSCRIBE" "users:322d3a41-c34c-46fe-aa7f-c3aff180a569"
SERVICE B - "PUBLISH" "users:6efc4e14-6679-49d9-be16-97246e02d32b" "request||where||[[\"id\",\"!=\",-65535]]"
SERVICE A - "PUBLISH" "users:6efc4e14-6679-49d9-be16-97246e02d32b" "response||[{\"id\":2,\"name\":\"Nayeli Kulas\",\"email\":\"dare.edgar@example.com\",\"email_verified_at\":\"2020-10-20T13:48:39.000000Z\",\"password\":\"\",\"created_at\":\"2020-10-20T13:48:39.000000Z\",\"updated_at\":\"2020-10-20T13:48:39.000000Z\"}]
SERVICE B - "PUNSUBSCRIBE" "users:6efc4e14-6679-49d9-be16-97246e02d32b"
```
1. Service A subscribes to all ```users``` channel with all package IDs.
2. Service B creates package `322d3a41-c34c-46fe-aa7f-c3aff180a569` and subscibes to it's channel
3. Service B sends to package's channel request `request||where||[[\"id\",\"!=\",-65535]]`
4. Service A, that parsed request calls method `where` firstly in Controller, in if not present in bound Model, generates Collection of response.
5. Generated response Service A sends to needed channel.
6. Service B unsubscribes package's channel.

Each package should have id, and as we cannot implement numerical inctementing id we just create UUID(for ex.: 322d3a41-c34c-46fe-aa7f-c3aff180a569)

### How is Protocol looks

`request||where||[[\"id\",\"!=\",-65535]]`
this string means that received redis Pub event is actually 'Request', and within it's Controller we should call 'where' method, with arguments '\[\[\"id\",\"!=\",-65535]]'

Argument can be not one, for example: `request||where||api_token||12345_token`
