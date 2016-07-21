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

    Route::any('/test', function() {

        $type = \Illuminate\Support\Facades\Input::get('type');
        $data = \Illuminate\Support\Facades\Input::get('data');
        $bot = new \BotTelegram\bot\BotTelegram();
        var_dump($bot->_sendRequest($type, $data));
    });

});

Route::any('/bot-telegram/hook', ['as' => 'bot-telegram-hook','uses' => 'BotTelegram\Controllers\BotRequestController@hook', 'middleware'=>['web']]);
