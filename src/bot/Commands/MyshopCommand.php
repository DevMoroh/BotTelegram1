<?php

namespace BotTelegram\bot\Commands;

use BotTelegram\bot\BotTelegram as Bt;
use BotTelegram\bot\Command;
use BotTelegram\bot\Entities\InlineKeyboardButton;
use BotTelegram\bot\Entities\InlineKeyboardMarkup;
use BotTelegram\bot\Logger\TelegramLogger;
use BotTelegram\Models\CommandsTelegram;
use BotTelegram\Models\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Mockery\CountValidator\Exception;

class MyshopCommand extends TopshopCommand{

    public static $command = '/myshop';

    public function execute() {

        $message = $this->getMessage();
        $user_telegram = $message->getFrom();
        $_salt = \Config('telegram_bot.salt');
        $user = UserService::where('external_id', '=', $user_telegram->id)->first();
        //var_dump($user);

        if($user && $user->hash_user) {
            $token = app('AuthBotTelegram')->encrypt_hash($_salt, $user->hash_user);

            $_shops = $this->getMyShops($token, $user->letyshops_user_id);

            $inline_keyboard = [];
            $ms = "";

            if ($_shops) {

                if($_shops) {
                    $inline_keyboard['inline_keyboard'] = [];
                    $s = [];
                    foreach ($_shops as $k=>$shop) {
                        //  $ms .= " <b>{$shop['title']}</b> \n ".$shop['link']."\n";
                        $s[] =  ['text' => $shop['title'], 'url'=>"https://letyshops.modelfak.bissdata-home.com/".$shop['activate_url']];
                        if(count($s) > 1) {
                            $inline_keyboard['inline_keyboard'][] = $s;
                            $s = [];
                        }
                    }
                }

                $markup = json_encode($inline_keyboard, true);

                $r = $this->telegram->sendAswer(self::$command, ['disable_web_page_preview'=>true, 'reply_markup'=>$markup]);
            }
        }

    }

    /**
     * Получение своих рекомендуемых магазов
     * @param $token
     * @return mixed
     */
    protected function getMyShops($token, $uid = 0) {

        $shops = $this->getShops(true);
        $shops = json_decode( $shops , true);
        if(!$shops) return FALSE;

        $service_url_shop_top = \Config('telegram_bot.api_url') . '/myshops?uid='.$uid.'&token=' . $token;
        $result = app('AuthBotTelegram')->sendDataToLety($service_url_shop_top);
        $my_shops = $result['result'];

        $my_shops = json_decode( $my_shops , true);
        if(!$my_shops) return FALSE;

        foreach ($my_shops as $key=>$my_shop) {
            $my_shops[$key] = (int)$my_shop;
        }

        //var_dump($my_shops);exit;

        if(is_array($shops) && is_array($my_shops)) {
            $my_shops_formated = collect($shops)->whereIn('id', $my_shops);

//            $shops_formated = collect($shops)->keyBy('id');
//            $top_shops_formated = collect($top_shops)->map(function ($top_shop_id) {
//                return $shops_formated[$top_shop_id];
//            });
            return $my_shops_formated;
        }

        return FALSE;
    }
}