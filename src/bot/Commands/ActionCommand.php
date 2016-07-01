<?php


namespace BotTelegram\bot\Commands;

use BotTelegram\bot\BotTelegram as Bt;
use BotTelegram\bot\Command;
use BotTelegram\Models\CommandsTelegram;

class ActionCommand extends Command{

    public static $command = '/action';

    public function execute() {

        $commandDb = CommandsTelegram::where('type', self::$command)
            ->where('status', 1)->first();
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        if($commandDb) {
            $this->telegram->sendMessage([
                'text' => $commandDb->message,
                'chat_id' => $chat_id
            ]);
        }
    }
}