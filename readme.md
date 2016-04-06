# Sacky Server

This package is built to enhance more and transforming RatchetPHP package to an easy way.

Right now we have a single interpreter which handles a ``channel`` feature.

### INDEX
- [Installation](#installation)
- [The Socket Manager](#the-socket-manager)
- [Interpreters](#interpreters)
   - [Using AbstractChannel](#using-abstract_channel)
- [Actual Testing](#actual-testing)
- [Contribute](#contribute)

---

<a name="installation"></a>
# Installation

Right now, I can't provide a release yet or a branch-alias yet in packagist.org, but you may clone this project in your local and run a

```shell
composer install
```

<a name="the-socket-manager"></a>
# The Socket Manager

We have a socket manager that handles all the interpreters, to make this short, check the code below:

```php
<?php

use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$manager = new Socket\SocketManager([
    'chat' => [
        'component' => new HttpServer(new WsServer(new SampleChat)),
        'address'   => '0.0.0.0',
        'port'      => '8080',
    ],
]);

// let start the chat
$manager->call('chat')->run();
```

This is useful when you'll be creating a Service Provider from your framework such as Phalcon-Slayer / Laravel / Symfony and many more.

---

<a name="interpreters"></a>
# Interpreters

This lives all the abstract classes, it should be designed as a bridge/wrapper, it should have a unique way to handle informations came from browser's socket request.

<a name="using-abstract_channel"></a>
### Using AbstractChannel

If you'll be digging into the ``SampleChat`` class located at the root of this package, we're extending the ``AbstractChannel`` class, which implements the ``MessageComponentInterface`` in ratchet package.

You must declare the abstract functions:
- abstract function beforeEmit($client, $channel, $message);
- abstract function beforeListen($client, $channel, $message);
- abstract function beforeLeave($client, $channel, $message);

The ``beforeEmit()`` will be triggered once someone in the ``$channel`` tries to send a ``$message``
The ``beforeListen()`` will be triggered once someone would like to listen on a ``$channel`` and the ``$message`` will be some informations of the browser requester, or maybe a user's name, etc.
The ``beforeLeave()`` will be triggered once someonce would like to leave a ``$channel``

---

<a name="actual-testing"></a>
# Actual Testing

At first, you must execute the file ``run`` inside the ``tests/`` folder, it should be like this in your console

```php
./run
```

As we can't produce any automated tests yet as it requires time to implement as well, I think this should be tested thru actual testing.

To start, clone this project [sacky-client](https://github.com/php-pure/sacky-client) or download it.

Open **channel.html** from the ``sacky-client`` atleast 2 browser tabs for them to communicate to each other, a channel **#general** will be listened and should be printed inside your CLI console.

---

# Contribute

I would love to see some class interpreters and a client requester in the sacky-client repo, it will really help us to enhance more this package having a lot of options to work around not just a channel base.
