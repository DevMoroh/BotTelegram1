<?php



//Route::get('/bot1', ['prefix'=>'bot-telegram','namespace' => 'BotTelegram\Controllers']);



Route::group(['prefix'=>'bot-telegram','namespace' => 'BotTelegram\Controllers', 'middleware'=>['web', 'auth']], function() {

    Route::any('/', ['as' => 'bot-telegram-index','uses' => 'BotRequestController@index']);

    Route::any('/hook', ['as' => 'bot-telegram-hook','uses' => 'BotRequestController@hook']);

    Route::get('/users_list', ['as'=>'bot-telegram-users_list','uses' => 'BotRequestController@users_list']);

    Route::get('/commands_list', ['as'=>'bot-telegram-commands_list','uses' => 'BotRequestController@commands_list']);

    Route::get('/notifications_list', ['as'=>'bot-telegram-notifications_list','uses' => 'BotRequestController@notifications_list']);

    Route::resource('commands', 'TelegramCommandController');

    Route::resource('notifications', 'NotificationsController');

    Route::resource('users', 'UserServiceController');

    Route::get('fileentry', 'FileEntryController@index');
    Route::get('fileentry/get/{filename}/{type}', ['as' => 'getentry', 'uses' => 'FileEntryController@get']);
    Route::post('fileentry/add/{id}/{type}',['as' => 'addentry', 'uses' => 'FileEntryController@add'])
        ->where('id', '[0-9]+');
    Route::get('fileentry/issetfiles/{id}/{type}',  ['as' => 'issetfiles', 'uses' =>'FileEntryController@issetFiles'])->where('id', '[0-9]+');
    Route::get('fileentry/delete/{id}',  ['as' => 'deletefiles', 'uses' =>'FileEntryController@delete'])->where('id', '[0-9]+');

    Route::post('sendNotifications', 'BotRequestController@sendNotifications');

//    Route::get('startEvent', function() {
//        $data = [
//            'topic_id'=>'kittensCategory',
//            'data'=>'test'
//        ];
//
//        \BotTelegram\Socket\Pusher::sendDataToServer($data);
//       // phpinfo();
//        var_dump($data);
//
//    });

   // Route::get('/commands', ['as'=>'bot-telegram-commands','uses' => 'TelegramCommandController@commands']);
//
//    Route::get('/notifications', ['as'=>'bot-telegram-users','uses' => 'BotRequestController@notifications']);
//
//    Route::get('/messages', ['as'=>'bot-telegram-users','uses' => 'BotRequestController@messages']);

    Route::any('/setHook', function() {

            $url = \Illuminate\Support\Facades\Input::get('url');
            $bot = new \BotTelegram\bot\BotTelegram();
            var_dump($bot->setHook(['url'=>$url]));
    });

    Route::any('/getUpdates', function() {

        $bot = new \BotTelegram\bot\BotTelegram();
        return $bot->getData(true);
    });

});