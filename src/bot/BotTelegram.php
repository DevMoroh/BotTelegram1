<?php

namespace BotTelegram\bot;

//use BotTelegram\Request;

require_once __DIR__.'/Request.php';

class BotTelegram {
    use Request;

    protected $token = '';
    protected $botname = '';
    protected $pathCommands = '/Commands';
  //  protected $request = null;

    public function __construct($token, $botname)
    {
            $this->token = $token;
            $this->botname = $botname;
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
    public function listenHook() {
        $data = $this->getData();
        if(is_dir($this->pathCommands)) {
               
        }
        
    }

}