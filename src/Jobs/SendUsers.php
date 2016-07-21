<?php

namespace BotTelegram\Jobs;

use App\Jobs\Job;
use BotTelegram\Models\SendUser;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

//use BotTelegram\Socket\Pusher;

class SendUsers extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $users;
    protected $notifications;
    protected $send_note;
    protected $count;
    protected $status;
    public $queue = 'send';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($users, $notifications, $send_note, $status, $count)
    {
        $this->users = $users;
        $this->notifications = $notifications;
        $this->send_note = $send_note;
        $this->status = $status;
        $this->count = $count;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->send($this->users);
    }

    protected function send($user) {
        $BotTelegram = app('BotTelegram');
        $result = false;
        $notifications = $this->notifications;

        if ($notifications->photo) {
            
            $result = $BotTelegram->_sendFile('sendPhoto', [
                'caption' => $notifications->message,
                'chat_id' => $user->chat,
                
            ], $notifications->photo_path);
            //dd($result);

        } elseif ($notifications->message) {
            $result = $BotTelegram->sendMessage([
                'text' => $notifications->message,
                'chat_id' => $user->chat,
                'parse_mode' => 'HTML'
            ]);
        }

        $this->reconnectDb();

            /* Запись в лог отработых отправок */
        if($result->isOk()) {
            if ($this->send_note) {
//                $counts = $this->send_note->counts;
//                $counts++;
//                $this->send_note->update([
//                    'counts' => $counts
//                ]);
//                $this->send_note->save();
            }
            $m = 'Send ok!';
        }else{
            $m = $result->printError();
        }
//        SendUser::create([
//            'send_notice_id'=>$this->send_note->id,
//            'user_service_id'=>$user->external_id,
//            'notification_id'=>$notifications->id,
//            'message'=>$m
//        ]);

        if($this->status == 'last') {
            $notifications->update(['start'=>0]);
            $notifications->save();
            if($notifications->status_send > 0 AND $notifications->start !== 1) {
                $notifications->update(['status_send' => 2]);
                $notifications->save();
            }
        }
       
        $this->pushFire($user);

       // var_dump($result);
        return $result;
    }

    /*  метод рассылает уведомления по всем киентам о сосотоянии процесса рассылки */
    protected function pushFire($user) {
        $notifications = $this->notifications;
        $data = [
            'topic_id' => 'sendNotifications',
            'data' => [
                'username'=>$user->first_name,
                'notification'=>$notifications->name,
                'id'=>$notifications->id,
                'count'=>$this->count,
                'status'=>$this->status
//                'param'=>$this->job->()
             ]
        ];
        //Pusher::sendDataToServer($data);
    }
    public function reconnectDb() {
        try {
            DB::connection('mysql')->getDatabaseName();
        }
        catch (\PDOException $e) {
            DB::reconnect('mysql');
        }
    }

}
