<?php

namespace BotTelegram\bot\Commands;

use BotTelegram\bot\BotTelegram as Bt;
use BotTelegram\bot\Command;
use BotTelegram\bot\Entities\InlineKeyboardButton;
use BotTelegram\bot\Entities\KeyboardButton;
use BotTelegram\bot\Entities\ReplyKeyboardMarkup;
use BotTelegram\bot\Exception\TelegramException;
use BotTelegram\bot\Exception\StartException;
use BotTelegram\Models\CommandsTelegram;
use BotTelegram\Models\UserService;

class StartCommand extends Command{

    public static $command = '/start';

    /**
     *
     */
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

        try {
            $result = $this->subcommand($message);
            if(!$result) {
                $this->telegram->sendAswer(self::$command, [
                    'reply_markup' => $in . ''
                ]);
            }
        }catch (StartException $e){
            $message = $e->getMessage();
            echo json_encode(['text'=>$message, 'code'=>$e->getCode()]);
            $result = $this->telegram->sendMessage([
                'text'=>"Ошибка! Такой аккаунт уже привязан либо не существует!",
                'chat_id'=>$chat_id
            ]);
        }catch (\Exception $e) {
            var_dump($e->getFile()." - ".$e->getLine());
        }

    }

    protected function subcommand($message) {
        $text = $message->getText(true);
        if(!$text) return false;

        if($text && strpos($text, '-') === false) {
            throw new StartException("Invalid data format", 1);
        }

        $text = explode('-', $text);
        $user_id = $text[0];
        $token = $text[1];
        $_salt = \Config('telegram_bot.salt');

        $data = $this->getHashUser($user_id);
        $hash = $data['hash'];

        if(!empty($hash) && preg_match('/^[a-f0-9]{32}$/', $hash)) {
            throw new StartException("Invalid data format must be md5", 2);
        }

        if(!$data || !isset($data['hash']) || !isset($data['user_id'])) {
            throw new StartException("Invalid data from letyshops -- not found hash or user_id", 3);
        }

        if ($token !== md5($user_id . $hash . $_salt)) {
            throw new StartException("hash is not valid", 4);
        }


        $user_service = UserService::where('external_id', '=', $message->getFrom()->getId())
            ->first();

        if (!$user_service) {
            throw new StartException("table user_service do not have this external_id", 5);
        }

        $user_hash = UserService::where('hash_user', '=', $hash)
            ->first();

        if($user_hash) {
            throw new StartException("this user alredy has related accaunt", 6);
        }

        if (!empty($user_service->hash_user)) {
            throw new StartException("this user alredy has related to telegram", 7);
        }

        $user_service->update([
            'hash_user' => $data['hash'],
            'letyshops_user_id' => $user_id
        ]);
        return $user_service->save();
    }

    protected function getHashUser($uid) {
        $url = \Config('telegram_bot.api_url');
        $service_url = $url.$uid;

        $curl = curl_init($service_url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "bissdata:bissdata"); //Your credentials goes here
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //IMP if the url has https and you don't want to verify source certificate

        $curl_response = curl_exec($curl);
        $curl_error = curl_error($curl);
        $curl_errno = curl_errno($curl);
        if($curl_response == false) {
            throw new StartException("error curl $curl_errno - $curl_error", 8);
        }

        $response = json_decode($curl_response, true);
        curl_close($curl);

        return $response;
    }
}