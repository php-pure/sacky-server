<?php

require __DIR__.'/vendor/autoload.php';
require 'SampleChat.php';

use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$manager = new Socket\SocketManager([
    'chat' => [
        'component' => new HttpServer(new WsServer(new SampleChat)),
        'address'   => '0.0.0.0',
        'port'      => '8080',
    ],
]);

$chat = $manager->call('chat');

$chat->run();
