<?php


namespace BotTelegram\Socket;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class BaseSocket implements MessageComponentInterface {

    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "new message --- {$msg} \n";
        foreach ($this->clients as $client) {
            if ($from != $client) {
                $client->send($msg);
            }
        }
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo "add new client --- {$conn->resourceId} \n";
        $this->clients->attach($conn);
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo "close client --- {$conn->resourceId} \n";
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo $e->getMessage();
        $conn->close();
    }
}