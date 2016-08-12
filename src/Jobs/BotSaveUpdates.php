<?php namespace BotTelegram\Jobs;

use App\Commands\Command;

use BotTelegram\bot\Entities\Message;
use BotTelegram\bot\Entities\User;
use BotTelegram\Models\MessagesTelegram;
use BotTelegram\Models\UserService;
use BotTelegram\Socket\Pusher;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class BotSaveUpdates extends Command implements ShouldQueue {

	use InteractsWithQueue, SerializesModels;



	public $type;
	public $update;
	public $queue = 'update';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($update, $type)
	{
		$this->type = $type;
		$this->update = $update;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		//$this->reconnectDb();

		app('BotTelegram')->handle($this->update, $this->type);
	}

	/*  метод рассылает уведомления по всем киентам о сосотоянии процесса рассылки */
//	protected function pushFire() {
//		$message = $this->message;
//		$user = $this->user;
//		$data = [
//			'topic_id' => 'sendUpdates',
//			'data' => [
//				'username'=>$user->username,
//				'message'=>$message->getText(),
//				'type'=>$message->getType(),
//				'id'=>$message->getMessageId()
//			]
//		];
//		//Pusher::sendDataToServer($data);
//	}

	public function reconnectDb() {
		try {
			DB::connection()->getDatabaseName();
		}
		catch (\PDOException $e) {
			DB::reconnect();
		}
	}

}
