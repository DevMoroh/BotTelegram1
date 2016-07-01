<?php

namespace BotTelegram\Socket;

use ZMQContext;

class Pusher extends BasePusher {

    static function sendDataToServer(array $data) {
        $zmq = new ZMQContext();

        $socket = $zmq->getSocket(\ZMQ::SOCKET_PUSH, 'my push');
        //$socket->connect('tcp://127.0.0.1:5555');
       // $socket->setSockOpt(\ZMQ::SOCKOPT_LINGER, 30); //ADDED
        $socket->connect("tcp://127.0.0.1:5555");
        $data = json_encode($data);
        $socket->send( $data );
    }

    public function broadcast($data) {
        $data = json_decode($data, true);

        $subscribes = $this->getSubscribesTasks();

        if(isset($subscribes[ $data['topic_id'] ])) {

            $topic = $subscribes[ $data['topic_id'] ];
            $topic->broadcast($data);
        }
    }
}