<?php

namespace BotTelegram\Controllers;
use BotTelegram\Requests\TelegramRequest;
use BotTelegram\bot\Test;
use BotTelegram\Jobs\SendNotifications;
use BotTelegram\Models\Notifications;
use BotTelegram\Models\SendUser;
use BotTelegram\Models\TagsModel;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;

class BotRequestController extends Controller{

    use DispatchesJobs;
    protected $bot;
    protected $manager;

    public function __construct()
    {

    }

    public function index() {

        return View::make('bot-telegram::index');
    }


    public function users_list () {

        return View::make('bot-telegram::users');
    }

    public function commands_list() {
        $tags = TagsModel::all();
        return View::make('bot-telegram::commands', ['tags'=>$tags]);
    }


    public function notifications_list() {
        var_dump(Auth::user()->id);
        return View::make('bot-telegram::notifications');
    }

    public function messages_list() {
        return View::make('bot-telegram::messages');
    }

    public function notifications_logs() {
        return View::make('bot-telegram::send-note');
    }

    public function tags_list() {

        return View::make('bot-telegram::tags_list');
    }

    public function send_users($id) {
        $users = SendUser::with(['user', 'notification'])->where(
            [
                'send_notice_id'=>$id
            ]
        )->get();

        return response()->json($users);
    }

    public function sendNotifications(Request $request) {

//        $notifications = Notifications::where('status', 1)
//            ->first();
//        $notifications->photo;
        // $result = Artisan::call('queue:listen');
        $input = $request->input();
        $notification = Notifications::where('status', 1)->find($input['id']);
        if(!$notification){
            return response()->json(['text'=>'Данного уведомления несуществует....', 'status'=>'FAIL']);
        }

        
        if($notification->start == 1) {
            return response()->json(['text'=>"Рассылка <b>{$notification->name}</b> уже запущена....", 'status'=>'FAIL']);
        }
        $notification->start = 1;

        $notification->update($input);
        $notification->save();
        if($notification->start == 1) {

            $this->dispatch(
                new SendNotifications($notification)
            );
        }
        return response()->json(['text'=>"Рассылка <b>{$notification->name}</b> запущена успешно!", 'status'=>'OK']);
    }

    /**
     * @param TelegramRequest $request
     * @return string
     */
    public function sendMessage(TelegramRequest $request) {
        $input = $request->input();
        $token = $input['token'];
        $text = $input['text'];

        $user = app('AuthBotTelegram')->isAuth($token);
        if($user) {
            app('BotTelegram')->sendMessage([
                'text'=>$text,
                'chat_id'=>$user->chat
            ]);
            return response()->json(['result'=>true, 'message'=>'success!'], 201);
        }

        return response()->json(['result'=>false, 'message'=>'fail!'], 201);

    }
    
    public function sendNotificationsSchedule(){}
    
    public function sendTest() {

        $test = new Test();

        $test->send();
    }

    public function addTestAccounts() {

        $mid = 1000000;
        $from = 1000000;
        $date = time();
        $chatid = 1000000;

        for($i=0; $i < 1000; $i++) {
            $mid++;
            $from++;
            $chatid++;

//            MessagesTelegram::create([
//                'message_id' => $mid,
//                'from'=>$from,
//                'date'=>$date,
//                'chat'=>$chatid,
//                'type'=>'test',
//                'text'=>'test message'
//            ]);
//
//            UserService::create([
//                'first_name'=>'Test',
//                'last_name'=>'Test',
//                'time_create'=>$date,
//                'external_id'=>$from,
//                'status'=>'1',
//                'service'=>'telegram'
//            ]);
        }
    }

    public function hook() {
        $BotTelegram = app('BotTelegram');
        $BotTelegram->startRequest();

        return 'OK';
    }

}