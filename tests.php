<?php

require __DIR__.'/vendor/autoload.php';

$manager = new Socket\SocketManager(
    require __DIR__.'/src/config.sample.php'
);

$chat = $manager->call('schat');

$chat->run();
