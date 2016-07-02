<?php

namespace BotTelegram\Socket;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class BasePusher implements WampServerInterface {

    protected $subscribesTasks = [];

    public function getSubscribesTasks() {
        return $this->subscribesTasks;
    }

    public function addSubscribesTasks($task) {
        $this->subscribesTasks[$task->getId()] = $task;
    }

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->addSubscribesTasks($topic);
    }
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onOpen(ConnectionInterface $conn) {
    }
    public function onClose(ConnectionInterface $conn) {
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        echo "New psuh {$topic->getId()}";
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {

        echo $e->getMessage();
    }
}