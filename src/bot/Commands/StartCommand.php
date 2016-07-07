<?php

namespace BotTelegram\bot\Commands;

use BotTelegram\bot\BotTelegram as Bt;
use BotTelegram\bot\Command;
use BotTelegram\bot\Entities\InlineKeyboardButton;
use BotTelegram\bot\Entities\KeyboardButton;
use BotTelegram\bot\Entities\ReplyKeyboardMarkup;
use BotTelegram\Models\CommandsTelegram;
use BotTelegram\Models\UserService;

class StartCommand extends Command{

    public static $command = '/start';

    public function execute()
    {
        // TODO: Implement execute() method.
            $message = $this->getMessage();
            $chat_id = $message->getChat()->getId();
            $userid = $message->getFrom();

        $keyboard_button = [
            new KeyboardButton(['text' => 'Комманда']),
            new KeyboardButton(['text' => 'Комманда 2'])
        ];

        $in = new ReplyKeyboardMarkup(['keyboard' => [$keyboard_button], 'resize_keyboard'=>true, 'one_time_keyboard'=>true]);

        $this->telegram->sendAswer(self::$command, [
            'chat_id'=>$chat_id,
            'reply_markup'=>$in.''
        ], $message->getMessageId());
    }
}