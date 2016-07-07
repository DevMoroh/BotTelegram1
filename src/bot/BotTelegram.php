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
use BotTelegram\Models\MessagesTelegram;
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


    public function startRequest() {
        $data = $this->getDataInput();
        $update = new Update($data, $this->botname);
        $this->dispatch(
            new BotSaveUpdates($update, 'telegram')
        );
    }

    public function handle(Update $update, $typeService) {

        echo "handler Bot\n";

        $this->update = $update;
        $type =  $this->update->getUpdateType();

        var_dump($type);

        switch($type) {
            case 'edited_message':
                $message =  $this->update->getEditedMessage();
//                    Event::fire(new BotGeneration($message->getFrom(), $message, 'telegram'));
                $m = MessagesTelegram::where(
                    [
                        'message_id'=>$message->getMessageId(),
                        'text'=>$message->getText(true)
                    ])->get()->toArray();
                if(empty($m)) {

                    $this->saveUpdates($message, $message->getFrom(), $typeService);

                    $type_mess = $message->getType();
                    // var_dump($type_mess);exit;
                    if ($type_mess === 'command') {
                        $command = $message->getCommand();

                        $this->executeCommand($command, $message);
                    }
                }
                break;
            case 'message':
                   $message =  $this->update->getMessage();
//                    Event::fire(new BotGeneration($message->getFrom(), $message, 'telegram'));
                   $m = MessagesTelegram::where(['message_id'=>$message->getMessageId()])->get()->toArray();
                   if(empty($m)) {

                       $this->saveUpdates($message, $message->getFrom(), $typeService);

                       $type_mess = $message->getType();
                       // var_dump($type_mess);exit;
                       if ($type_mess === 'command') {
                           $command = $message->getCommand();

                           $this->executeCommand($command, $message);
                       }
                   }
                break;
        }



        //$logger = new TelegramLogger();
        //$logger->writeLog(, 'test');
        //var_dump();

    }

    public function saveUpdates(Message $message, User $user, $type) {

           $issetuser = UserService::where(
               [
                   'external_id'=>$user->id,
                   'service'=>$type
               ])
               ->first();
           if(empty($issetuser)) {
               UserService::create([
                   'first_name' => $user->first_name,
                   'last_name' => $user->last_name,
                   'service' => $type,
                   'external_id' => $user->id,
                   'username' => $user->username,
                   'chat' => $message->getChat()->getId(),
                   'status' => 1,
                   'subscribe' => 1
               ])->save();
           }else{
               $issetuser->update([
                   'first_name' => $user->first_name,
                   'last_name' => $user->last_name,
                   'username' => $user->username,
                   'chat' => $message->getChat()->getId()
               ]);
               $issetuser->save();
           }

        $m = MessagesTelegram::where(
            [
                'message_id'=>$message->getMessageId(),
            ])->first();
        if(empty($m)) {
            MessagesTelegram::create([
                'message_id' => $message->getMessageId(),
                'date' => $message->getDate(),
                'type' => $message->getType(),
                'command' => $message->getCommand(),
                'from' => $message->getFrom()->id,
                'text' => $message->getText(),
                'chat' => $message->getChat()->getId()
            ])->save();
        }else{
            $m->update([
                'type' => $message->getType(),
                'command' => $message->getCommand(),
                'text' => $message->getText(),
            ]);
            $m->save();
        }
            //$this->pushFire();
    }

    public function executeCommand($command, $message) {
        $object = $this->getCommndObject($command);
        if($object && $object instanceof Command) {
            $object->execute();
        }else{
           $this->sendAswer('/' . $command, [
               'chat_id'=>$message->getChat()->getId(),
           ], $message->getMessageId());
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

    /* 
    *   Отправка ответа на команды и простые сообщения
    */
    public function sendAswer($command, array $data, $message_id) {
       $answere = CommandsTelegram::where('type', $command)->where('status', 1)->first();
        //var_dump($answere);

        if($answere) {
            $data['text'] = $answere->message;
            $data['parse_mode'] = 'HTML';
            $result = $this->sendMessage($data);
            if($result['data']['ok']) {
                $mid = $result['data']['result']['message_id'];
                $message = MessagesTelegram::where(
                    [
                       'message_id'=>$message_id,
                    ])->first();
                if($message) {
                    $message->update(['answer' => $answere->message, 'approved'=>1, 'message_answer_id'=>$mid]);
                    $message->save();
                }
            }
        }
    }

}