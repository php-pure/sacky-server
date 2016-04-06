<?php

use Ratchet\ConnectionInterface;
use Socket\Interpreters\AbstractChannel;

/**
 * @author  Daison Cariño <daison12006013@gmail.com>
 *
 * protected getConnections()       - Should return all the connected clients
 * protected getListeners($channel) - Should return all the listeners by channel name
 */
class SampleChat extends AbstractChannel
{
    /**
     * {@inheritdoc}
     */
    // public function onOpen(ConnectionInterface $client)
    // {
    //     $this->connect($client);

    //     echo "A new connection ({$client->resourceId})\n";
    // }

    /**
     * {@inheritdoc}
     */
    // public function onClose(ConnectionInterface $client)
    // {
    //     $this->destroy($client);

    //     echo "Connection {$client->resourceId} has benn disconnected\n";
    // }

    /**
     * {@inheritdoc}
     */
    // public function onError(ConnectionInterface $client, Exception $e)
    // {
    //     echo "An error has occurred: {$e->getMessage()}\n";

    //     $conn->close();
    // }

    /**
     * {@inheritdoc}
     */
    public function onEmit($from, $message, $channel)
    {
        echo $message."\n";

        $this->emit($from, $message, $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function onListen($client, $channel, $message)
    {
        $this->listen($client, $channel);

        # in our js, we're passing 'name' in the object
        $messageArr = json_decode($message, true);

        if (isset($messageArr['name'])) {
            echo "Client '{$messageArr['name']}' listens in channel '{$channel}'\n";
            return;
        }

        echo "Listening on channel {$channel} by client {$client->resourceId}\n";
    }

    /**
     * {@inheritdoc}
     */
    public function onLeave($client, $channel, $message)
    {
        $this->destroy($client, $channel);

        # in our js, we're passing 'name' in the object
        $messageArr = json_decode($message, true);

        if (isset($messageArr['name'])) {
            echo "Client '{$messageArr['name']}' leaves the channel '{$channel}'\n";
            return;
        }

        echo "Leaving channel {$channel} by client {$client->resourceId}\n";
    }
}