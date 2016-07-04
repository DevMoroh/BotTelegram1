<?php
namespace BotTelegram\bot;

//use BotTelegram as BotTelegram;

use BotTelegram\bot\Exception\TelegramException;
use BotTelegram\bot\Logger\TelegramLogger;

trait Request {

    private static $types = [
        'sendAudio'=>'audio',
        'sendPhoto'=>'photo'
    ];

    public static $_URL = 'https://api.telegram.org/bot';
    public $test_url = 'http://192.168.88.58:8080';

    public function _sendTestRequest($func, $data = []) {
        $out = null;

        if( $curl = curl_init() ) {
            $url = $this->test_url.'/'.$func;
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            $out = curl_exec($curl);
            curl_close($curl);
        }
        return json_decode($out, true);
    }

    public function _sendRequest($func, $data = []) {

        $out = null;
        
        if( $curl = curl_init() ) {
            $url = self::$_URL.$this->token.'/'.$func;
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $out = curl_exec($curl);
            $curl_error = curl_error($curl);
            $curl_errno = curl_errno($curl);
            if(config('app.debug')) {
                $verbose_curl_output = fopen('php://temp', 'w+');
                curl_setopt($curl, CURLOPT_VERBOSE, true);
                curl_setopt($curl, CURLOPT_STDERR, $verbose_curl_output);

                rewind($verbose_curl_output);
                $verboseLog = stream_get_contents($verbose_curl_output);
                fclose($verbose_curl_output);
                TelegramLogger::writeDb('Verbose curl output:' . htmlspecialchars($verboseLog));
            }
            curl_close($curl);

            if ($out === false) {
                throw new TelegramException($curl_error, $curl_errno);
            }
        }
        return json_decode($out, true);
    }

    public function _sendFile($type, $data, $file) {

        $result = null;
        if (!is_null($file)) {
            $data[self::$types[$type]] = self::encodeFile($file);
            $result = $this->_sendRequest($type, $data);
        }

        //$url        = $bot_url . "sendPhoto?chat_id=" . $chat_id ;

       // $data['photo'] = '@'.$path;

//        var_dump($data);exit;
//        $ch = curl_init();
//
//        $array2=array(self::$types[$type]=>$path);
//        $ch = curl_init();
//
//        curl_setopt($ch, CURLOPT_URL, $url);
//        $this->curl_custom_postfields($ch, $data, $array2);
//        $output=curl_exec($ch);
//        curl_close($ch);
        return $result;
    }

    protected static function encodeFile($file)
    {
        return new \CURLFile($file);
    }

    /* Для отправки сообщений */

    private function curl_custom_postfields($ch, array $assoc = array(), array $files = array()) {

        // invalid characters for "name" and "filename"
        static $disallow = array("\0", "\"", "\r", "\n");

        // build normal parameters
        foreach ($assoc as $k => $v) {
            $k = str_replace($disallow, "_", $k);
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"",
                "",
                filter_var($v),
            ));
        }

        // build file parameters
        foreach ($files as $k => $v) {
            switch (true) {
                case false === $v = realpath(filter_var($v)):
                case !is_file($v):
                case !is_readable($v):
                    continue; // or return false, throw new InvalidArgumentException
            }
            $data = file_get_contents($v);
            $v = call_user_func_array("end", explode(DIRECTORY_SEPARATOR, $v));
            $k = str_replace($disallow, "_", $k);
            $v = str_replace($disallow, "_", $v);
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
               // "Content-Type: audio/mpeg",
                "",
                $data,
            ));
        }

        // generate safe boundary
        do {
            $boundary = "---------------------" . md5(mt_rand() . microtime());
        } while (preg_grep("/{$boundary}/", $body));

        // add boundary for each parameters
        array_walk($body, function (&$part) use ($boundary) {
            $part = "--{$boundary}\r\n{$part}";
        });

        // add final boundary
        $body[] = "--{$boundary}--";
        $body[] = "";

        // set options
        return @curl_setopt_array($ch, array(
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => implode("\r\n", $body),
            CURLOPT_HTTPHEADER => array(
                "Expect: 100-continue",
                "Content-Type: multipart/form-data; boundary={$boundary}", // change Content-Type
            ),
        ));
    }

    /*
     * Метод получает данные которые приходят сверху от телеграмма
    */
    public function getData($all = false) {
        if($all) return $this->_sendRequest('getUpdates');

        $data = null;
        $update = file_get_contents('php://input');

        if($update) {
            $update = json_decode($update, TRUE);


            $chatId = $update["message"]["chat"]["id"];
            $type = $update["message"]["chat"]["type"];
            $message = $update["message"]["text"];
            $entities = $update["message"]["entities"];

            $data = [
                'chat_id' => $chatId,
                'message' => $message,
                'type' => $type,
                'entities' => $entities
            ];
        }

        return $data;
    }

    public static function generateGeneralFakeServerResponse(array $data = null)
    {
        //PARAM BINDED IN PHPUNIT TEST FOR TestServerResponse.php
        //Maybe this is not the best possible implementation

        //No value set in $data ie testing setWebhook
        //Provided $data['chat_id'] ie testing sendMessage

        $fake_response = ['ok' => true]; // :)

        if (!isset($data)) {
            $fake_response['result'] = true;
        }

        //some data to let iniatilize the class method SendMessage
        if (isset($data['chat_id'])) {
            $data['message_id'] = '1234';
            $data['date'] = '1441378360';
            $data['from'] = [
                'id'         => 123456789,
                'first_name' => 'botname',
                'username'   => 'namebot',
            ];
            $data['chat'] = ['id' => $data['chat_id']];

            $fake_response['result'] = $data;
        }

        return $fake_response;
    }

    public function getDataInput() {
        $data = file_get_contents('php://input');
        if($data) {
            $logger = new TelegramLogger();
            $logger->writeLog($data, 'updates');
        }
//        var_dump(json_decode($data, true));exit;
        return json_decode($data, true);
    }

}