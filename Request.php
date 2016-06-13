<?php
namespace BotTelegram;

//use BotTelegram as BotTelegram;

trait Request {

    private static $types = [
        'sendAudio'=>'audio',
        'sendPhoto'=>'photo'
    ];

    private static $_URL = 'https://api.telegram.org/bot';

    public function _sendRequest($func, $data) {

        $out = null;
        
        if( $curl = curl_init() ) {
            $url = self::$_URL.$this->token.'/'.$func;
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            $out = curl_exec($curl);
            curl_close($curl);
        }
        return json_decode($out, true);
    }

    public function _sendFile($type, $data, $path) {

        $url = self::$_URL.$this->token.'/'.$type; $output = null;
        //$url        = $bot_url . "sendPhoto?chat_id=" . $chat_id ;

       // $data['photo'] = '@'.$path;

//        var_dump($data);exit;
        $ch = curl_init();

        $array2=array(self::$types[$type]=>$path);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $this->curl_custom_postfields($ch, $data, $array2);
        $output=curl_exec($ch);
        curl_close($ch);
        return $output;
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
            $v = call_user_func("end", explode(DIRECTORY_SEPARATOR, $v));
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
    public function getData() {
        $data = null;
        $update = file_get_contents('php://input');
        if($update) {
            $update = json_decode($update, TRUE);


            $chatId = $update["message"]["chat"]["id"];
            $message = $update["message"]["text"];

            $data = [
                'chat_id' => $chatId,
                'message' => $message
            ];
        }

        return $data;
    }

}