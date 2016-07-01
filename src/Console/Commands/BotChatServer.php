<?php namespace BotTelegram\Console\Commands;

use BotTelegram\Socket\BaseSocket;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class BotChatServer extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'botchat';

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
		$this->info("Start server!");

		$server = IoServer::factory(new HttpServer(new WsServer(new BaseSocket())), 8080);
		$server->run();
	}

}
