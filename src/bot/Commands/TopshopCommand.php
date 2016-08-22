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

class TopshopCommand extends Command{

    public static $command = '/topshop';

    public function execute() {

        $message = $this->getMessage();
        $user_telegram = $message->getFrom();
        $_salt = \Config('telegram_bot.salt');
        $user = UserService::where('external_id', '=', $user_telegram->id)->first();
        //var_dump($user);

        if($user && $user->hash_user) {
            $token = app('AuthBotTelegram')->encrypt_hash($_salt, $user->hash_user);

            $_shops = $this->getShopsTop($token);

            $inline_keyboard = [];
            $ms = "";
            //var_dump($shops);
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
     * Получение всех магазинов
     * @param bool $remember
     * @return mixed
     */
    protected function getShops($remember = true) {

        $cache_shop = Cache::get('shops');
        if(!$cache_shop) {
            $service_url_shop = \Config('telegram_bot.api_url') . '/shops';
            $result = app('AuthBotTelegram')->sendDataToLety($service_url_shop);
            $shops = $result['result'];

            $expiresAt = Carbon::now()->addMinutes(\Config('telegram_bot.expires_at_cache_minute'));
            if($remember && $shops) { Cache::put('shops', $shops, $expiresAt); }

            return $shops;
        }
        return $cache_shop;
    }

    /**
     * Получение топовых магазов
     * @param $token
     * @return mixed
     */
    protected function getShopsTop($token) {

        $shops = $this->getShops(true);
        $shops = json_decode( $shops , true);
        if(!$shops) return FALSE;

        $service_url_shop_top = \Config('telegram_bot.api_url') . '/topshops?token=' . $token;
        $result = app('AuthBotTelegram')->sendDataToLety($service_url_shop_top);
        $top_shops = $result['result'];

        $top_shops = json_decode( $top_shops , true);
        if(!$top_shops) return FALSE;

        if(is_array($shops) && is_array($top_shops)) {
            $top_shops_formated = collect($shops)->whereIn('id', $top_shops);

//            $shops_formated = collect($shops)->keyBy('id');
//            $top_shops_formated = collect($top_shops)->map(function ($top_shop_id) {
//                return $shops_formated[$top_shop_id];
//            });
            return $top_shops_formated;
        }

        return FALSE;
    }
}