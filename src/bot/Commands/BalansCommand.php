<?php

namespace BotTelegram\bot\Commands;

use BotTelegram\bot\BotTelegram as Bt;
use BotTelegram\bot\Command;
use BotTelegram\bot\Entities\InlineKeyboardButton;
use BotTelegram\bot\Entities\InlineKeyboardMarkup;
use BotTelegram\bot\Logger\TelegramLogger;
use BotTelegram\Models\CommandsTelegram;
use BotTelegram\Models\UserService;

class BalansCommand extends Command{

    public static $command = '/balans';

    public function execute() {

        $message = $this->getMessage();
        $user_telegram = $message->getFrom();
        $_salt = \Config('telegram_bot.salt');
        $user = UserService::where('external_id', '=', $user_telegram->id)->first();

        if($user && $user->hash_user) {
            $token = app('AuthBotTelegram')->encrypt_hash($_salt, $user->hash_user);

            $balans = $this->getBalans($token);

            var_dump($balans);

            if ($balans['result']) {
                $this->telegram->sendAswer(self::$command, ['text' => $balans['result']]);
            }
        }else{
            $this->telegram->sendAswer(self::$command, ['text' => "Привяжите ваш аккаунт к letyshops.ru по ссылке - https://letyshops.modelfak.bissdata-home.com/telegram"]);
        }
    }

    protected function getBalans($token) {

        //$token = app('AuthBotTelegram')->base64url_encode($token);

        $service_url = \Config('telegram_bot.api_url').'/balans?token='.$token;
        return app('AuthBotTelegram')->sendDataToLety($service_url);
    }
}