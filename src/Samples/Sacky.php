<?php
namespace Socket\Samples;

use Exception;
use SplObjectStorage;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Sacky implements MessageComponentInterface
{
    protected $clients = [];

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients[$conn->resourceId]['conn'] = $conn;

        echo "A new connection ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $parsedMsg = json_decode($msg, true);

        # check $parsedMsg if has '__listen__'
        # update the $this->clients
        if (isset($parsedMsg['__listen__'])) {
            $this->clients[$from->resourceId]['channels'][$parsedMsg['__listen__']] = true;

            return;
        }

        echo $msg;

        foreach ($this->clients as $resourceId => $client) {
            $hasAccessToChannel = isset($client['channels'][$parsedMsg['channel']]) ? true : false;

            if ($hasAccessToChannel === true) {
                $client['conn']->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        unset($this->clients[$conn->resourceId]);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
