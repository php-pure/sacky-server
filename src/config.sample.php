<?php

use Socket\Samples\Chat;
use Ratchet\Http\Router;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

# This is just a sample array that you could probably
# use when calling $socket = (new SocketManager)->classes([...]);
return [
    'schat' => [
        'component' => new HttpServer(new WsServer(new Chat)),
        'port'     => '8080',
        'address'  => '0.0.0.0',
    ],
];
