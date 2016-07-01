<?php

namespace BotTelegram\bot\Commands;

use BotTelegram\bot\BotTelegram as Bt;

class InfoCommand extends Bt{

    public static $command = 'info';

    public function execute() {

       $data = $this->getData();

        $this->sendMessage([
            'text'=>'You get message!',
            'chat_id'=>$data['chat_id']
        ]);
    }
}