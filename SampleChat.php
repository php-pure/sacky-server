<?php

use Ratchet\ConnectionInterface;
use Socket\Interpreters\AbstractChannel;

/**
 * @author  Daison Cariño <daison12006013@gmail.com>
 *
 * protected getConnections()       - Should return all the connected clients
 * protected getListeners($channel) - Should return all the listeners by channel name
 * protected emit()                  -
 * public    onEmit()                -
 * protected listen()                -
 * public    onListen()              -
 * protected destroy()               -
 * public    onDestroy()             -
 */
class SampleChat extends AbstractChannel
{
    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $client)
    {
        $this->connect($client);

        echo "A new connection ({$client->resourceId})\n";
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $client)
    {
        $this->destroy($client);

        echo "Connection {$client->resourceId} has disconnected\n";
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $client, Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    /**
     * {@inheritdoc}
     */
    public function onEmit(ConnectionInterface $from, $msg, $channel)
    {
        echo $msg;

        $this->emit($from, $msg, $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function onListen(ConnectionInterface $client, $channel)
    {
        echo "Listening on channel {$channel} of {$client->resourceId}\n";

        $this->listen($client, $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function onDestroy(ConnectionInterface $client, $channel)
    {
        echo "Destroying channel {$channel} of {$client->resourceId}\n";

        $this->destroy($client, $channel);
    }
}