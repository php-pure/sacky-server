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

        $client->close();
    }

    /**
     * {@inheritdoc}
     */
    final public function onMessage(ConnectionInterface $from, $msg)
    {
        $parsedMsg = json_decode($msg, true);

        # check $parsedMsg if has '__listen__'
        # update the $this->clients
        if (isset($parsedMsg['__listen__'])) {
            $rawMsg = $parsedMsg;
            unset($rawMsg['__listen__']);

            $this->beforeListen($from, $parsedMsg['__listen__'], json_encode($rawMsg));
            return;
        }

        # check $parsedMsg if has '__destroy__'
        # unset the passed channel
        if (isset($parsedMsg['__leave__'])) {
            $rawMsg = $parsedMsg;
            unset($rawMsg['__leave__']);

            $this->beforeLeave($from, $parsedMsg['__leave__'], json_encode($rawMsg));
            return;
        }

        $rawMsg = $parsedMsg;
        unset($rawMsg['channel']);

        $this->beforeEmit($from, $parsedMsg['channel'], json_encode($rawMsg));
    }

    /**
     * This connects the client in the stack
     *
     * @param mixed $client
     * @return mixed
     */
    public function connect(ConnectionInterface $client)
    {
        $this->clients[$client->resourceId]['socket'] = $client;

        return $this;
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

        # check if the index 'socket' exists, if not, pass it in
        if (!isset($this->clients[$client->resourceId]['socket'])) {
            $this->clients[$client->resourceId]['socket'] = $client;
        }
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
     * @return mixed
     */
    protected function getClients()
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

        foreach ($this->getClients() as $resourceId => $client) {
            if (isset($client['channels'][$channel])) {
                $listeners[$resourceId] = $client;
            }
        }

        return $listeners;
    }

    /**
     * Triggered when someone sent a message
     *
     * @param mixed $client
     * @param string $channel
     * @param string $message
     * @return void
     */
    abstract function beforeEmit($client, $channel, $message);

    /**
     * When someone would like to listen on a channel
     *
     * @param mixed $client
     * @param string $channel
     * @param string $message
     * @return void
     */
    abstract function beforeListen($client, $channel, $message = null);

    /**
     * When someone would like to leave a channel
     *
     * @param mixed $client
     * @param string $channel
     * @param string $message
     * @return void
     */
    abstract function beforeLeave($client, $channel, $message = null);
}
