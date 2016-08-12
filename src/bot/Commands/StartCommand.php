<?php

namespace BotTelegram\bot\Commands;

use BotTelegram\bot\BotTelegram as Bt;
use BotTelegram\bot\Command;
use BotTelegram\bot\Entities\InlineKeyboardButton;
use BotTelegram\bot\Entities\KeyboardButton;
use BotTelegram\bot\Entities\ReplyKeyboardMarkup;
use BotTelegram\bot\Exception\TelegramException;
use BotTelegram\bot\Exception\StartException;
use BotTelegram\bot\Logger\TelegramLogger;
use BotTelegram\Models\CommandsTelegram;
use BotTelegram\Models\UserService;
use Illuminate\Support\Facades\DB;

class StartCommand extends Command{

    public static $command = '/start';
    public $auth;
    
    /**
     * Коды ошибок и рашифровка
     *
     * 1 - не правильный формат присланного токена
     * 2 - не правильный формат данных присланные с апи
     * 3 - не правильный формат хеша присланный с летишопса
     * 4 - не валидный токен
     * 5 - пользователя телеграм не существует в текущей базе
     * 6 - этот пользователь летишопс привязан уже к другому аккаунту телеграм
     * 7 - Этот пользователь телеграм уже юзает другой аккаунт летишопс
     * 8 - этот пользователь уже привязан и использует этот хеш
     * 9 - ошибка CURL запроса
     */
    
    public function execute()
    {
        // TODO: Implement execute() method.
            $message = $this->getMessage();
            $chat_id = $message->getChat()->getId();
            $userid = $message->getFrom();

        $keyboard_button = [
            new KeyboardButton(['text' => '/help']),
            new KeyboardButton(['text' => '/balans']),
            new KeyboardButton(['text' => '/topshop']),
            new KeyboardButton(['text' => '/myshop']),
        ];

        try {
            $result = $this->subcommand($message);

            $this->telegram->sendAswer(self::$command, [
                'reply_markup' => $this->getKeyBoard($keyboard_button) . ''
            ]);
        }catch (StartException $e){
            $message = $e->getMessage();
            $res = ['text'=>$message, 'code'=>$e->getCode()];

            echo json_encode($res);

            $result = $this->telegram->sendMessage([
                'text'=>"Ошибка! Такой аккаунт уже привязан либо не существует!",
                'chat_id'=>$chat_id,
                'reply_markup' => $this->getKeyBoard($keyboard_button) . ''
            ]);
            TelegramLogger::writeLog($res, 'starts');
        }catch (\Exception $e) {
            var_dump($e->getMessage()." - ".$e->getFile()." - ".$e->getLine());
        }

    }

    public function getKeyBoard($keyboard_button) {
        return new ReplyKeyboardMarkup(['keyboard' => [$keyboard_button]]);
    }
    
    public function subcommand($message) {
        
        $text = $message->getText(true);
        if(!$text) return false;

        $hash = '';
        $user_id = '';
        $auth = app('AuthBotTelegram');
        
        if(!preg_match('/^([a-zA-Z0-9\-\_]{64})$/', $text)) {
            throw new StartException("Invalid data format", 3);
        }

        $_salt = \Config('telegram_bot.salt');
        
        $result_token = $auth->decript_hash($_salt, $text);
        //dd($result_token);

        if($result_token) {
            $data = $this->getHashUser($result_token);
            $hash = $data['hash'];
            $user_id = $data['uid'];
        }else{
            throw new StartException("hash is incorrect", 2);
        }
        
        DB::enableQueryLog();
        
        $user_service = UserService::where('external_id', '=', $message->getFrom()->getId())
            ->first();

        if (!$user_service) {
            throw new StartException("table user_service do not have this external_id", 5);
        }
        
        $user_hash = UserService::where('hash_user', '=', $hash)
            ->where('hash_user','!=','null')
            ->where('external_id', '!=', $message->getFrom()->getId())
            ->first();

        if($user_hash) {
            throw new StartException("this user alredy has related by other accaunt telegram", 6);
        }

        if (!empty($user_service->hash_user)) {

            if($user_service->hash_user == $hash)
                 throw new StartException("this user alredy has related to telegram and he alredy has this hash", 8);
            else
                throw new StartException("this user alredy has related to telegram and he alredy has some hash", 7);
        }


        $user_service->update([
            'hash_user' => $hash,
            'letyshops_user_id' => $user_id
        ]);
        return $user_service->save();
    }

    public function getHashUser($token) {

        $token = app('AuthBotTelegram')->base64url_encode($token);

        $service_url = \Config('telegram_bot.api_url').'?token='.$token;
        //var_dump(file_get_contents($service_url));
        var_dump($service_url);
        $curl = curl_init($service_url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "bissdata:bissdata"); //Your credentials goes here
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Host: letyshops.modelfak.bissdata-home.com'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //IMP if the url has https and you don't want to verify source certificate

        $curl_response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        $curl_errno = curl_errno($curl);

        if($curl_response === false || $curl_response == NULL) {
            throw new StartException("error curl $curl_errno - $curl_error - http-status: $httpcode", 9);
        }

        $response = json_decode($curl_response, true);
        curl_close($curl);

        if($response === false) {
            throw new StartException("Bad response from ".$service_url, 10);
        }

        TelegramLogger::writeLog($curl_response.' - '.$service_url, 'api_lety');

        return $response;
    }
}