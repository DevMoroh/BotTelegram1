<?php

namespace BotTelegram\Jobs;

use App\Jobs\Job;
use BotTelegram\Models\Notifications;
use BotTelegram\Models\SendNote;
use BotTelegram\Models\UserService;

use BotTelegram\Socket\Pusher;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

//date_default_timezone_set('Europe/Kiev');

class SendNotifications extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, DispatchesJobs;


    protected $notification;
    public $queue = 'getuser';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Notifications $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(UserService $user)
    {
        //$queue = $this->job->getQueue();
        //var_dump($queue);
        $notifications = $this->notification;

        $users = $user::where('status', 1)
            ->where('external_id', '!=', 0)
            ->where('subscribe', 1)
//            ->where('id', '=', 3757)
            ->get();

        if(!$notifications || !$users) return ;

        if ($users) {
            $date = new \DateTime(null, new \DateTimeZone('Europe/Kiev') );

            $send_note = SendNote::create([
                'notification_id'=>$notifications->id,
                'status_send'=>2,
                'time_send'=>$date->getTimestamp()
            ]);
            $this->count = $uc = count($users);
            foreach ($users as $key => $user) {
                $index = $key + 1;
                $status = ($uc > $index) ? 'next' : 'last';
                $this->dispatch(new SendUsers($user, $notifications, $send_note, $status, $index));
                //Artisan::call('queue:work', ['--queue' => $queue]);
            }
        }
        
//        Pusher::sendDataToServer([
//            'topic_id' => 'sendNotifications',
//            'data'=>['sdfs']
//        ]);
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
