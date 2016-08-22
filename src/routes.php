<?php



//Route::get('/bot1', ['prefix'=>'bot-telegram','namespace' => 'BotTelegram\Controllers']);



Route::group(['prefix'=>'bot-telegram','namespace' => 'BotTelegram\Controllers', 'middleware'=>['web', 'auth']], function() {

    Route::any('/', ['as' => 'bot-telegram-index','uses' => 'BotRequestController@index']);

    Route::get('/users_list', ['as'=>'bot-telegram-users_list','uses' => 'BotRequestController@users_list']);

    Route::get('/commands_list', ['as'=>'bot-telegram-commands_list','uses' => 'BotRequestController@commands_list']);

    Route::get('/notifications_list', ['as'=>'bot-telegram-notifications_list','uses' => 'BotRequestController@notifications_list']);

    Route::get('/messages_list', ['as'=>'bot-telegram-messages_list','uses' => 'BotRequestController@messages_list']);

    Route::get('/notifications_logs', 'BotRequestController@notifications_logs');

    Route::get('/tags_list', 'BotRequestController@tags_list');

    Route::get('/send_users/{id}', 'BotRequestController@send_users');

    Route::get('/sendNotificationsSchedule', ['as'=>'bot-telegram-send_notifications_schedule','uses' => 'BotRequestController@sendNotificationsSchedule']);

    Route::resource('commands', 'TelegramCommandController');

    Route::resource('notifications', 'NotificationsController');

    Route::resource('users', 'UserServiceController');

    Route::resource('messages', 'MessagesTelegram');

    Route::resource('send-notes', 'SendNoteController');

    Route::resource('tags', 'TagsController');

    Route::put('notifications/startAt/{id}', 'NotificationsController@startAt');

    Route::get('fileentry', 'FileEntryController@index');
    Route::get('fileentry/get/{filename}/{type}', ['as' => 'getentry', 'uses' => 'FileEntryController@get']);
    Route::post('fileentry/add/{id}/{type}',['as' => 'addentry', 'uses' => 'FileEntryController@add'])
        ->where('id', '[0-9]+');
    Route::get('fileentry/issetfiles/{id}/{type}',  ['as' => 'issetfiles', 'uses' =>'FileEntryController@issetFiles'])->where('id', '[0-9]+');
    Route::get('fileentry/delete/{id}',  ['as' => 'deletefiles', 'uses' =>'FileEntryController@delete'])->where('id', '[0-9]+');

    Route::post('sendNotifications', ['uses'=>'BotRequestController@sendNotifications', 'middleware'=>['logdb']]);

    Route::any('/setHook', function() {

            $url = \Illuminate\Support\Facades\Input::get('url');
            $bot = new \BotTelegram\bot\BotTelegram();
            var_dump($bot->setHook(['url'=>$url]));
    });

    Route::any('/getUpdates', function() {

        $bot = new \BotTelegram\bot\BotTelegram();
        return $bot->getData(true);
    });

    Route::any('/sendUpdate', function() {

        $command = (isset($_GET['command'])) ? $_GET['command'] : '/help';
        $update = '{ "update_id":679940511,
                     "message":{
                            "message_id":'.mt_rand(10043, 99999).',
                             "from":{
                                      "id":231647617,
                                       "first_name":"Roma",
                                       "last_name":"Modelfak",
                                       "username":"modelfak"
                                     },
                             "chat":{ 
                                      "id":231647617,
                                      "first_name":"Roma",
                                      "last_name":"Modelfak",
                                      "username":"modelfak",
                                      "type":"private"
                                    },
                             "date":1466067290,
                             "text":"'.$command.'",
                             "entities":[{"type":"bot_command","offset":0,"length":7}]
                             }       
                     }';

        $client = new \GuzzleHttp\Client();
        try{
            $response = $client->post("http://localhost:8000/bot-telegram/hook", ['body' => $update, 'future' => true]);
            $r = $response->getBody()->getContents();
        }catch(\GuzzleHttp\Exception\ServerException $e) {
            $r = $e->getMessage();
        }
        return $r;

    });

});

Route::any('/bot-telegram/sendMessage', ['uses'=>'BotTelegram\Controllers\BotRequestController@sendMessage', 'middleware'=>['web']]);

Route::any('/bot-telegram/hook', ['as' => 'bot-telegram-hook','uses' => 'BotTelegram\Controllers\BotRequestController@hook', 'middleware'=>['web']]);
