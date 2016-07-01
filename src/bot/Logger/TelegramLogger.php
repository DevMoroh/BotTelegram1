<?php

namespace BotTelegram\bot\Logger;


use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class TelegramLogger {

    public function writeBrowserLog($data) {

        // create a log channel

        $log = new Logger('name');
        $log->pushHandler(new BrowserConsoleHandler);

        $log->addWarning('Foo'. var_export($data, true));
    }


    public function writeLog($data, $filename){

        $log = new Logger('name');
        $log->pushHandler(new StreamHandler(__DIR__.'/logs/'.$filename.'.log', Logger::WARNING));

            $log->addWarning('Foo' . var_export($data, true));
    }
}