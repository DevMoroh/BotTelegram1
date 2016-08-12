<?php

namespace BotTelegram\bot\Commands;

use BotTelegram\bot\BotTelegram as Bt;
use BotTelegram\bot\Command;
use BotTelegram\bot\Entities\InlineKeyboardButton;
use BotTelegram\bot\Entities\InlineKeyboardMarkup;
use BotTelegram\bot\Logger\TelegramLogger;
use BotTelegram\Models\CommandsTelegram;
use BotTelegram\Models\UserService;

class TopshopCommand extends Command{

    public static $command = '/topshop';

    public function execute() {

        $message = $this->getMessage();
        $user_telegram = $message->getFrom();
        $_salt = \Config('telegram_bot.salt');
        $user = UserService::where('external_id', '=', $user_telegram->id)->first();
        //var_dump($user);

        if($user && $user->hash_user || true) {
            $token = app('AuthBotTelegram')->encrypt_hash($_salt, $user->hash_user);

            //$shops = $this->getShops($token);
            $shops = '[
                       {"title":"Aliexpress","link":"https:\/\/letyshops.modelfak.bissdata-home.com\/shops\/aliexpress"},
                       {"title":"Aliexpress","link":"https:\/\/letyshops.modelfak.bissdata-home.com\/shops\/aliexpress"},
                       {"title":"Aliexpress","link":"https:\/\/letyshops.modelfak.bissdata-home.com\/shops\/aliexpress"},
                       {"title":"Aliexpress","link":"https:\/\/letyshops.modelfak.bissdata-home.com\/shops\/aliexpress"},
                       {"title":"Aliexpress","link":"https:\/\/letyshops.modelfak.bissdata-home.com\/shops\/aliexpress"},
                       {"title":"Aliexpress","link":"https:\/\/letyshops.modelfak.bissdata-home.com\/shops\/aliexpress"},
                       {"title":"Aliexpress","link":"https:\/\/letyshops.modelfak.bissdata-home.com\/shops\/aliexpress"},
                       {"title":"Aliexpress","link":"https:\/\/letyshops.modelfak.bissdata-home.com\/shops\/aliexpress"}
                      ]';
            $inline_keyboard = [];
            $ms = "";
            //var_dump($shops);
            if ($shops) {
                $shops = json_decode($shops, true);
                if($shops) {
                    foreach ($shops as $shop) {
                        //  $ms .= " <b>{$shop['title']}</b> \n ".$shop['link']."\n";
                        $s = [];
                        $s[] = new InlineKeyboardButton(['text' =>$shop['title'], 'url' => $shop['link']]);
                        $inline_keyboard[] = $s;
                    }
                }

                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' =>  'Aliexpress', 'url'=>'https://letyshops.modelfak.bissdata-home.com/shops/aliexpress'],
                            ['text' => 'GearBest', 'url'=>'https://letyshops.modelfak.bissdata-home.com/shops/aliexpress']
                        ],
                        [
                            ['text' => 'Rozetka', 'url'=>'https://letyshops.modelfak.bissdata-home.com/shops/aliexpress'],
                            ['text' => 'Booking.com', 'url'=>'https://letyshops.modelfak.bissdata-home.com/shops/aliexpress']
                        ]
                    ],
                ];
                $markup = json_encode($keyboard, true);

                $in = new InlineKeyboardMarkup(['inline_keyboard' => [$inline_keyboard]]);
                $this->telegram->sendAswer(self::$command, ['disable_web_page_preview'=>true, 'reply_markup'=>$markup]);
            }
        }

    }

    protected function getShops($token) {

        //$token = app('AuthBotTelegram')->base64url_encode($token);

        $service_url = \Config('telegram_bot.api_url').'/topshops?token='.$token;
        app('AuthBotTelegram')->sendDataToLety($service_url);
    }
}