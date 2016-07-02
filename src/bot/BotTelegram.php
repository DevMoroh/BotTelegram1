<?php

namespace BotTelegram\bot;

//use BotTelegram\Request;

//use App\Events\BotGeneration;
use App\Jobs\BotSaveUpdates;
use BotTelegram\bot\Entities\Message;
use BotTelegram\bot\Entities\Update;
use BotTelegram\bot\Entities\User;
use BotTelegram\bot\Exception\TelegramException;
use BotTelegram\bot\Logger\TelegramLogger;
use BotTelegram\Models\CommandsTelegram;
use BotTelegram\Models\Notifications;
use BotTelegram\Models\UserService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

require_once __DIR__.'/Request.php';

define('BASE_PATH', __DIR__);
define('BASE_COMMANDS_PATH', BASE_PATH . '/Commands');

class BotTelegram {
    use Request, DispatchesJobs;

    protected $token = '';
    protected $botname = '';
    protected $pathCommands = [];
    protected $update = null;
  //  protected $request = null;

    public function __construct()
    {
            $this->token = \Config('telegram_bot.token');
            $this->botname = \Config('telegram_bot.bot_name');
    }

    public function getMe() {
        $result = $this->_sendRequest('getMe', []);
        return $result;
    }

    public function setHook($data = []) {
        $result = $this->_sendRequest('setWebHook', $data);
        return $result;
    }

    public function sendMessage($data) {
        $result = $this->_sendRequest('sendMessage', $data);
        return $result;
    }

    public function getUpdates($data) {
        $result = $this->_sendRequest('getUpdates', $data);
        return $result;
    }

    /*
     * Метод подключает классы команд которые приходят сверху
     *  */
    public function getCommandsList() {

        $commands = [];
        array_unshift($this->pathCommands, BASE_COMMANDS_PATH);

            foreach ($this->pathCommands as $path) {
                try {
                    $files = new \RegexIterator(
                        new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($path)
                        ),
                        '/^.+Command.php$/'
                    );
                    foreach ($files as $file) {
                        $command = $this->sanitizeCommand(substr($file->getFilename(), 0, -11));
                        $command_name = strtolower($command);

                        $commands[] = $command_name;
                    }

                }catch (\Exception $e) {
                    throw new TelegramException('Error getting commands from path: ' . $path);
                }
            }

        return $files;
    }

    public function getCommndObject($command) {

        $command_namespace = __NAMESPACE__ . '\\Commands\\'. $this->ucfirstUnicode($command) . 'Command';
        if (class_exists($command_namespace)) {
            return new $command_namespace($this, $this->update);
        }

        return null;
    }


    public function handle() {

        $data = $this->getDataInput();
        $this->update = new Update($data, $this->botname);

        $type =  $this->update->getUpdateType();

        switch($type) {
            case 'message':
                    $message =  $this->update->getMessage();
//                    Event::fire(new BotGeneration($message->getFrom(), $message, 'telegram'));

                    $this->dispatch(
                        new BotSaveUpdates($message->getFrom(), $message, 'telegram')
                    );

                    $type_mess = $message->getType();
                   // var_dump($type_mess);exit;
                    if ($type_mess === 'command') {
                        $command = $message->getCommand();

                        $this->executeCommand($command, $message);
                    }
                break;
        }



        //$logger = new TelegramLogger();
        //$logger->writeLog(, 'test');
        //var_dump();

    }

    public function sendAll() {

//        $notifications = Notifications::where('status', 1)->all();
//        $users = UserService::where('status', 1)->all();
//        if($notifications) {
//                foreach ($notifications as $noti) {
////                    $this->sendMessage([
////                        'text' => $noti->message,
////                        'chat_id' => $chat_id
////                    ]);
//                }
//        }
    }

    public function executeCommand($command) {
        $object = $this->getCommndObject($command);
        if($object && $object instanceof Command) {
            $object->execute();
        }else{
           
                $this->sendAswer('/' . $command, $this->update->getMessage()->getChat()->getId());
        }
    }


    /**
     * Replace function `ucfirst` for UTF-8 characters in the class definition and commands
     *
     * @param string $str
     * @param string $encoding (default = 'UTF-8')
     *
     * @return string
     */
    protected function ucfirstUnicode($str, $encoding = 'UTF-8')
    {
        return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) . mb_strtolower(mb_substr($str, 1, mb_strlen($str), $encoding), $encoding);
    }

    /**
     * @todo Complete DocBlock
     */
    protected function sanitizeCommand($command)
    {
        return str_replace(' ', '', $this->ucwordsUnicode(str_replace('_', ' ', $command)));
    }


    /**
     * Replace function `ucwords` for UTF-8 characters in the class definition and commands
     *
     * @param string $str
     * @param string $encoding (default = 'UTF-8')
     *
     * @return string
     */
    protected function ucwordsUnicode($str, $encoding = 'UTF-8')
    {
        return mb_convert_case($str, MB_CASE_TITLE, $encoding);
    }

    public function sendAswer($command, $chat_id) {
       $answere = CommandsTelegram::where('type', $command)->where('status', 1)->first();
        if($answere) {
            $this->sendMessage([
                'text' => $answere->message,
                'chat_id' => $chat_id
            ]);
        }
    }

}