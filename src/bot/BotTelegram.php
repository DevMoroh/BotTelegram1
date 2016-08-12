<?php

namespace BotTelegram\bot;

//use BotTelegram\Request;

//use App\Events\BotGeneration;
use App\Jobs\BotSaveUpdates;
use BotTelegram\bot\Entities\Message;
use BotTelegram\bot\Entities\ServerResponse;
use BotTelegram\bot\Entities\Update;
use BotTelegram\bot\Entities\User;
use BotTelegram\bot\Exception\TelegramException;
use BotTelegram\bot\Logger\TelegramLogger;
use BotTelegram\Models\CommandsTelegram;
use BotTelegram\Models\MessagesTelegram;
use BotTelegram\Models\Notifications;
use BotTelegram\Models\UserService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
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

        $breaks = array("<br />","<br>","<br/>");
        $data['text'] = str_ireplace($breaks, "\r\n", $data['text']);
        $sp = ["&nbsp;", "&nbsp"];
        $data['text'] = str_ireplace($sp, " ", $data['text']);

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
        app('BotTelegram')->handle($update, 'telegram');
//        $this->dispatch(
//            new BotSaveUpdates($update, 'telegram')
//        );
    }

    public function handle(Update $update, $typeService) {

        echo "handler Bot\n";

        $this->update = $update;
        $type =  $this->update->getUpdateType();
        $command = '';

        var_dump($type);
        switch($type) {
            case 'edited_message':
                $message =  $this->update->getEditedMessage();
//                    Event::fire(new BotGeneration($message->getFrom(), $message, 'telegram'));
                $m = MessagesTelegram::where(
                    [
                        'message_id'=>$message->getMessageId(),
                        'text'=>$message->getText(true)
                    ])->first();
                if(!$m) {

                    $this->saveUpdates($message, $message->getFrom(), $typeService);

                    $type_mess = $message->getType();
                    // var_dump($type_mess);exit;
                    if ($type_mess === 'command') {
                        $command = $message->getCommand();
                        $this->executeCommand($command);
                    }else{
                        $this->wordParse($message);
                    }
                    //$this->preExecuteCommand($command, $message);
                }
                break;
            case 'message':
                   $message =  $this->update->getMessage();
//                    Event::fire(new BotGeneration($message->getFrom(), $message, 'telegram'));
                   $m = MessagesTelegram::where(['message_id'=>$message->getMessageId()])->first();

                   if(!$m) {
                       $this->saveUpdates($message, $message->getFrom(), $typeService);

                       $type_mess = $message->getType();
//                       var_dump($type_mess);
                       if ($type_mess === 'command') {
                           $command = $message->getCommand();
                           $this->executeCommand($command);
                       }else{
                           $this->wordParse($message);
                       }

                       //$this->preExecuteCommand($command, $message);
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
                   'external_id' => $user->getId(),
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

    public function preExecuteCommand($command = '', $message) {
        $typeCommand = \Config('telegram_bot.type');

        switch($typeCommand) {
            case "command":
                $this->executeCommand($command);
                break;
            case "word_parse":
                $this->wordParse($message);
                break;
        }
    }
    //SELECT ct.* FROM commands_telegram ct, tags, tags_objects `to` WHERE to.tag_id = tags.id AND to.object_id = ct.id AND tags.name = 'Летишопс';
    public function wordParse(Message $message) {

        DB::enableQueryLog();
        $text = $message->getText();
        $chat_id = $message->getChat()->getId();

        $result = "";
        $list = explode(" ", $text);
        if($list) {
            foreach ($list as $li) {
                if($li && mb_strlen($li, 'UTF-8') > 2) {
                    $result .= $li."* ";
                }
            }
        }

        $commands = CommandsTelegram::join('tags_objects', 'commands_telegram.id', '=', 'tags_objects.object_id')
            ->join('tags', function($join) {
                $join->on('tags.id', '=', 'tags_objects.tag_id');
            })
            ->whereRaw("MATCH(tags.name) AGAINST(? IN BOOLEAN MODE)",[$result])
            ->selectRaw("commands_telegram.id, commands_telegram.type, commands_telegram.name, (MATCH(tags.name) AGAINST(? IN BOOLEAN MODE)) as rel",[$result])
            ->groupBy('commands_telegram.id')
            ->orderBy('rel', 'DESC')
            ->get();

        //dd(DB::getQueryLog());
       // var_dump($commands);
        if($commands) {
              foreach ($commands as $command) {
                  $type = str_replace("/", "", $command->type);
                  var_dump($command->rel);
                  $this->executeCommand($type);
              }
        }
    }

    public function executeCommand($command) {
        $object = $this->getCommndObject($command);
//         dd($object);
        //var_dump($object);
        if($object && $object instanceof Command) {
            $object->execute();
        }else{
           $this->sendAswer('/' . $command);
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
    public function sendAswer($command, array $data = []) {
        $answere_message = '';
        if(!isset($data['text'])) {
            $answere = CommandsTelegram::where('type', $command)->where('status', 1)->first();
            $answere_message = ($answere) ? $answere->message : '';
        }else{
            $answere_message = $data['text'];
        }
        $_data = [];

        $message_telegram = $this->update->getMessage();
         if($answere_message) {
             $_data['text'] = "★★★  " . $answere_message . "\n\n• • •\n\n";
             $_data['parse_mode'] = 'HTML';
             //$_data['reply_to_message_id'] = $message_telegram->getMessageId();
             $_data['chat_id'] = $message_telegram->getChat()->getId();
             $data = array_merge($_data, $data);
             $result = $this->sendMessage($data);
             $this->saveAnswer($result);
         }
    }
    
    public function saveAnswer(ServerResponse $response) {
       // dd($response);
        if(!$response->isOk()) return false;

        $result = $response->getResult();
        if($result instanceof Message) {
            $answere = $result->getText();
            $message_telegram =  $this->update->getMessage();
            $message_id = $result->getMessageId();
            if($message_telegram) {
                $_message = MessagesTelegram::where(
                    [
                        'message_id'=>$message_telegram->getMessageId()
                    ]
                )->first();
                if($_message) {
                    $_message->update(['answer' => $answere, 'approved' => 1, 'message_answer_id' => $message_id]);
                    return $_message->save();
                }

                return false;
            }
        }

    }

}