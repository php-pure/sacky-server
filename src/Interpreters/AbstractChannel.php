<?php
namespace Socket\Interpreters;

use Exception;
use SplObjectStorage;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

abstract class AbstractChannel implements MessageComponentInterface
{
    protected $clients = [];

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
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $parsedMsg = json_decode($msg, true);

        # check $parsedMsg if has '__destroy__'
        # unset the passed channel
        if (isset($parsedMsg['__destroy__'])) {
            $this->onDestroy($from, $parsedMsg['__destroy__']);
            return;
        }

        # check $parsedMsg if has '__listen__'
        # update the $this->clients
        if (isset($parsedMsg['__listen__'])) {
            $this->onListen($from, $parsedMsg['__listen__']);
            return;
        }

        $this->onEmit($from, $msg, $parsedMsg['channel']);
    }

    /**
     * This connects the client in the stack
     *
     * @param mixed $client
     * @return void
     */
    public function connect(ConnectionInterface $client)
    {
        $this->clients[$client->resourceId]['socket'] = $client;
    }

    /**
     * This emit a message to all listeners based on the channel
     *
     * @param mixed $from
     * @param string  $msg
     * @param string  $channel
     * @return void
     */
    protected function emit(ConnectionInterface $from, $msg, $channel)
    {
        # iterate all listeners by the channel
        foreach ($this->getListeners($channel) as $resourceId => $client) {

            # now send it to them
            $client['socket']->send($msg);
        }
    }

    /**
     * This allows the client to listen from a channel
     *
     * @param mixed $client
     * @param string $channel
     * @return void
     */
    protected function listen(ConnectionInterface $client, $channel)
    {
        $this->clients[$client->resourceId]['channels'][$channel] = true;
    }

    /**
     * This destroys the client from a channel
     *
     * @param mixed $client
     * @param string $channel
     * @return void
     */
    protected function destroy(ConnectionInterface $client, $channel = null)
    {
        if ($channel !== null) {
            unset($this->clients[$client->resourceId]);
        }

        unset($this->clients[$client->resourceId]['channels'][$channel]);
    }

    /**
     * Return all connected clients
     *
     * @return [type] [description]
     */
    protected function getConnections()
    {
        return $this->clients;
    }

    /**
     * Return all listeners of the channel
     *
     * @param string $channel
     * @return mixed
     */
    protected function getListeners($channel)
    {
        $listeners = [];

        foreach ($this->getConnections() as $resourceId => $client) {
            if (isset($client['channels'][$channel])) {
                $listeners[$resourceId] = $client;
            }
        }

        return $listeners;
    }

    /**
     * When someone would like to listen on a channel
     *
     * @param string $channel
     * @return void
     */
    abstract public function onListen(ConnectionInterface $client, $channel);

    /**
     * When someone would like to destroy a channel
     *
     * @param string $channel
     * @return void
     */
    abstract public function onDestroy(ConnectionInterface $client, $channel);

    /**
     * Triggered when someone sent a message
     *
     * @param mixed $from
     * @param string $msg
     * @param string $channel
     * @return void
     */
    abstract public function onEmit(ConnectionInterface $from, $msg, $channel);
}
