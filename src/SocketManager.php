<?php
namespace Socket;

use Exception;
use Ratchet\Server\IoServer;
use InvalidArgumentException;
use React\Socket\Server as Reactor;
use React\EventLoop\Factory as LoopFactory;

class SocketManager
{
    private $sockets_config = [];

    public function __construct(array $sockets_config)
    {
        $this->sockets_config = $sockets_config;
    }

    public function newComponent($alias, $component)
    {
        $this->sockets_config[$alias] = $component;

        return $this;
    }

    public function call($alias)
    {
        if ( !isset($this->sockets_config[$alias]) ) {
            throw new InvalidArgumentException(
                "Socket Manager: alias [$alias] not found."
            );
        }

        $config = $this->sockets_config[$alias];

        # by default we will have our address as 0.0.0.0
        # and our port as 8080
        $component = $config['component'];
        $address   = isset($config['address']) ? $config['address'] : '0.0.0.0';
        $port      = isset($config['port']) ? $config['port'] : '8080';

        # now listen in the event loop
        # and also the socket
        $loop = LoopFactory::create();
        $socket = new Reactor($loop);
        $socket->listen($port, $address);

        return new IoServer($component, $socket, $loop);
    }
}
