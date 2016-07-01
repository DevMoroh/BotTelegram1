<?php namespace BotTelegram\Console\Commands;

use BotTelegram\Socket\BaseSocket;
use BotTelegram\Socket\Pusher;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as ReactLoop;
use React\ZMQ\Context;
use React\Socket\Server as ReactServer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class BotPushServer extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'botpush';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}



	public function handle()
	{
		$loop = ReactLoop::create();

		$pusher = new Pusher;

		$reactContext = new Context($loop);

		$pull = $reactContext->getSocket(\ZMQ::SOCKET_PULL);

//		$pull->bind('tcp://127.0.0.1:5555');
		$pull->bind('tcp://127.0.0.1:5555');

		$pull->on('message', [$pusher, 'broadcast']);

		$ReactServer = new ReactServer($loop);

		$ReactServer->listen(8081, '0.0.0.0');
		$this->info("Pusher starts!");
		$server = new IoServer(
			new HttpServer(
				new WsServer(
					new WampServer($pusher)
				)
			), $ReactServer);


		//var_dump($server);
		$loop->run();
		//$server->run();

	}

}
