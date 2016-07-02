<?php

namespace BotTelegram\Controllers;
use App\Jobs\SendNotifications;
use BotTelegram\bot\Test;
use BotTelegram\Console\Commands\Clearer;
use BotTelegram\Models\Notifications;
use BotTelegram\Models\UserService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\Queue\Factory as FactoryContract;

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
        return View::make('bot-telegram::commands');
    }


    public function notifications_list() {
        return View::make('bot-telegram::notifications');
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

            $users = UserService::where('status', 1)
                ->where('external_id', '!=', 0)
                ->where('subscribe', 1)
                ->limit(10)
                ->get();

            if ($users) {
                $uc = count($users);
                foreach ($users as $key => $user) {
                    $index = $key + 1;
                    $status = ($uc > $index) ? 'next' : 'last';
                    $this->dispatch(
                        new SendNotifications($user, $notification, $index, $status)
                    );
                    //Artisan::call('queue:work', ['--queue' => $queue]);
                }
            }
        }
        //$queries = DB::getQueryLog();
        //var_dump($queries);
        return response()->json(['text'=>"Рассылка <b>{$notification->name}</b> запущена успешно!", 'status'=>'OK']);
    }


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
        $BotTelegram->handle();
        
    }
    
}