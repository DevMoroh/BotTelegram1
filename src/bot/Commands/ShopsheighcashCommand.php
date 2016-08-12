<?php

namespace BotTelegram\bot\Commands;

use BotTelegram\bot\BotTelegram as Bt;
use BotTelegram\bot\Command;
use BotTelegram\bot\Entities\InlineKeyboardButton;
use BotTelegram\bot\Entities\InlineKeyboardMarkup;
use BotTelegram\Models\CommandsTelegram;

class ShopsheighcashCommand extends Command{

    public static $command = '/shopsheighcash';

    public function execute() {

        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

//        $inline_keyboard = [
//            new InlineKeyboardButton(['text' => 'inline', 'switch_inline_query' => 'true']),
//            new InlineKeyboardButton(['text' => 'callback', 'callback_data' => 'identifier']),
//            new InlineKeyboardButton(['text' => 'open url', 'url' => 'https://github.com/akalongman/php-telegram-bot']),
//        ];

        $inline_keyboard = [
            new InlineKeyboardButton(['text' => 'Супер кнопочка ))', 'url' => 'https://letyshops.ru/shops/category:254925']),
        ];

        $in = new InlineKeyboardMarkup(['inline_keyboard' => [$inline_keyboard]]);

        $this->telegram->sendAswer(self::$command, [
            'reply_markup'=>$in.''
        ]);

    }
}