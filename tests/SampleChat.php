<?php

use Ratchet\ConnectionInterface;
use Socket\Interpreters\AbstractChannel;

/**
 * @author  Daison Cariño <daison12006013@gmail.com>
 *
 * protected getClients()           - Should return all the connected clients
 * protected getListeners($channel) - Should return all the listeners by channel name
 */
class SampleChat extends AbstractChannel
{
    /**
     * {@inheritdoc}
     */
    public function beforeEmit($from, $channel, $message)
    {
        echo $message."\n";

        $this->emit($from, $message, $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeListen($client, $channel, $message = null)
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
    public function beforeLeave($client, $channel, $message = null)
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