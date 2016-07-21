<?php
/**
 * Created by PhpStorm.
 * User: Роман
 * Date: 25.06.2016
 * Time: 1:45
 */

namespace BotTelegram\Validators;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class BotValidator extends Validator {

//    public function validator(\Illuminate\Validation\Factory $factory)
//    {
//
//        Validator::extend('eachFile', function($attribute, $value, $parameters) {
//            foreach ( $value as $key => $image ) // add individual rules to each image
//            {
//                $this->messages[sprintf( 'files.%d', $key )] = 'Неверный тип файла должен быть '.implode(",", $parameters);
//                $result = $this->validateMimes(sprintf( 'files.%d', $key ), $image, $parameters);
//            }
//            return $result;
//        });
//
//        $validator = $factory->make(
//            $this->all(), $this->container->call([$this, 'rules']), $this->messages(), $this->attributes()
//        );
//
//        return $validator;
//    }

    public function validateEachFile($attribute, $value, $parameters) {

        $this->setCustomMessages(['each_file' => 'Неверный тип файла :variable должен быть '.implode(",", $parameters)]);
        foreach ( $value as $key => $image ) // add individual rules to each image
        {
            $result = $this->validateMimes(sprintf( 'files.%d', $key ), $image, $parameters);
        }
        return $result;
    }

    /*
     * Валидация даты по нескольким форматам
     *  */
    public function validateDateMultiFormat($attribute, $value, $formats) {
        // iterate through all formats
        foreach($formats as $format) {

            // parse date with current format
            $parsed = date_parse_from_format($format, $value);

            // if value matches given format return true=validation succeeded
            if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
                return true;
            }
        }

        // value did not match any of the provided formats, so return false=validation failed
        return false;
    }

    /*
     * date_diff
     *  */
    public function validateDateDiff($attribute, $value, $parameters) {

        if(!empty($value)) {
            $date_new = strtotime($value);
            $date_now = time();
            //$value = DB::table($table)->pluck($column);

            if($date_new <= $date_now)
                return false;
        }
        return true;
    }

    public function messages()
    {

        return parent::messages(); // TODO: Change the autogenerated stub
    }

}