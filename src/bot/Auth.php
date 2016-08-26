<?php

namespace BotTelegram\bot;

use BotTelegram\bot\Logger\TelegramLogger;
use BotTelegram\Models\UserService;

class Auth {

    public function isAuth($token) {
        $user = null;
        try {
            $result = $this->decript_hash(\Config('telegram_bot.salt'), $token);

            if($result) {
                $user = UserService::where('hash_user', $result, '=')->first();
                //var_dump($user);
            }
        }catch(\Exception $e) {
            // echo $e->getMessage();
            return false;
        }
        return ($user) ? $user : false;
    }

    public function decript_hash($key, $ciphertext_base64) {
        # --- DECRYPTION ---
//        $bcrypt = new Bcrypt();
//        $hash =   $bcrypt->hash($key);
//        $isGood = $bcrypt->verify('password', $hash);

        //echo md5($hash);exit;
        $key = pack('H*', md5($key));

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $ciphertext_dec = $this->base64url_decode($ciphertext_base64);

        # retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);

        # retrieves the cipher text (everything except the $iv_size in the front)
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);

        # may remove 00h valued characters from end of plain text
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key,
            $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

        //$this->verify($key);

        return $plaintext_dec;
    }

    public function encrypt_hash($key_for_hash, $plaintext) {

        //echo md5($hash);exit;
        $key = pack('H*', md5($key_for_hash));

        $key_size =  strlen($key);
        //echo "Key size: " . $key_size . "\n";

        # create a random IV to use with CBC encoding
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key,
            $plaintext, MCRYPT_MODE_CBC, $iv);

        # prepend the IV for it to be available for decryption
        $ciphertext = $iv . $ciphertext;

        $ciphertext_base64 = $this->base64url_encode($ciphertext);

        return $ciphertext_base64;
    }

    protected function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function sendDataToLety($service_url) {

        $curl = curl_init($service_url);
        if(env('APP_DEBUG')) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, "bissdata:bissdata"); //Your credentials goes here
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl, CURLOPT_POST, true);
//        curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //IMP if the url has https and you don't want to verify source certificate

        $curl_response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        $curl_errno = curl_errno($curl);

        if($curl_response === false || $curl_response == NULL) {
            var_dump($curl_error, $curl_errno);
        }
        $response = $curl_response;
        /* логирование респонсов с апишки */
        if($curl_response === false || $httpcode !== 200)
        {
            TelegramLogger::writeLog('Ошибка curl: ' . curl_error($curl) .' api_lety - code '.$httpcode, 'api_lety');
        }
        if( $response ) {
            TelegramLogger::writeLog($response, 'api_lety');
        }
        curl_close($curl);

        return [
            'result'=>$response,
            'code'=>$httpcode
        ];
    }

}