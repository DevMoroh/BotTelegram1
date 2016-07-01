<?php

namespace BotTelegram\Models;
use Illuminate\Database\Eloquent\Model;

class CommandsTelegram extends Model {


    protected $table = 'commands_telegram';

    protected $fillable = ['message', 'type', 'name', 'status', 'create_time'];

    public $timestamps = false;

    public static $validate = [
        'rules'=> [
            'name' => 'required',
            'type' => 'required',
            'message' => 'required',
        ],
        'messages'=> [
            'name.required' => 'Заполните поле Имя',
            'type.required' => 'Заполните поле Тип',
            'message.required' => 'Заполните поле Сообщения',
        ]
    ];

}