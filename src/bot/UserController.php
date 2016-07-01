<?php

namespace App\Http\Controllers;

use App\UsersService;
use BotTelegram\BotTelegram;
use BotTelegram\Command;
use BotTelegram\Entities\InlineKeyboardButton;
use BotTelegram\Entities\InlineKeyboardMarkup;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
//use Telegram\Bot\Api;

class UserController extends BaseController {

    public function show() {
        
        return View::make('user.index', array('user' => []));
    }

    public function bot() {
        $BotTelegram = new BotTelegram('187976149:-PA2j6cTU1vrZM', '');
        $result = $BotTelegram->getMe();

        $data = [
            'chat_id'=>'@test34567422',
            'text'=>'Test'
        ];
        $BotTelegram->sendMessage($data);




        return response()->json($result);
    }


    public function hook() {

        $BotTelegram = new BotTelegram('187976149:-PA2j6cTU1vrZM', '');
        $data = $BotTelegram->getData();
        $message = $data['message'];
        $chatId  = $data['chat_id'];

        $inline_keyboard = [
            new InlineKeyboardButton(['text' => 'inline', 'switch_inline_query' => 'true']),
            new InlineKeyboardButton(['text' => 'callback', 'callback_data' => 'identifier']),
            new InlineKeyboardButton(['text' => 'open url', 'url' => 'https://github.com/akalongman/php-telegram-bot']),
        ];

        switch($message) {

            case "/test":
                $data = [
                    'chat_id'=>$chatId,
                    'text'=>'test'
                ];
                $BotTelegram->sendMessage($data);
                break;
            case "/hi":
                $data = [
                    'chat_id'=>$chatId,
                    'text'=>'test'
                ];
                $BotTelegram->sendMessage($data);
                break;
            case "/getFile":
                $data = [
                    'chat_id'=>$chatId,
                    'caption'=>'Photo description'
                ];
                $file = __DIR__.'/files/test.jpeg';

                $BotTelegram->_sendFile('sendPhoto', $data, $file);
                break;
            default:
                 $in = new InlineKeyboardMarkup(['inline_keyboard' => [$inline_keyboard]]);
                $data = [
                    'chat_id'=>$chatId,
                    'text'=>'/some',
                    'reply_markup'=> $in.''
                ];
                $result = $BotTelegram->sendMessage($data);
        }


        $file = $_SERVER['DOCUMENT_ROOT'].'/logs/log.txt';
        // Открываем файл для получения существующего содержимого
        $current = file_get_contents($file);

        $d = date('Y-m-d H:i:s');
        $current .= "\n -----------------------------------------";
        $current .= "\n".$message." ----  $d";
        // Пишем содержимое обратно в файл
       // file_put_contents($file, $current, FILE_APPEND);

//        UsersService::create([
//            'username'=>'username',
//            'first_name'=>'firstName',
//            'last_name'=>'lastName',
//            'service'=>'telegram',
//            'external_id'=>'asd'
//        ]);

        //var_dump($data, $result);
        return response()->json($result);
    }


    public function setWebHook() {
        $BotTelegram = new BotTelegram('187976149:AAGd8bwRSezwFfp3ZYcIq-PA2j6cTU1vrZM', '');
        $response = $BotTelegram->setHook(['url' => 'https://php-modelfak.rhcloud.com/hook']);

        return response()->json($response);
    }

    public function getUpdates() {
        $BotTelegram = new BotTelegram('187976149:AAGd8bwRSezwFfp3ZYcIq-PA2j6cTU1vrZM', '');
        $response = $BotTelegram->getUpdates([]);
        return response()->json($response);
    }

}