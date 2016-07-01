<?php

namespace BotTelegram\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Notifications extends Model {

    protected $appends = ['photo', 'photo_path'];
    public $guarded = ['id'];

    protected $table = 'notifications';

    protected $fillable = ['message', 'type', 'name', 'status', 'create_time', 'start', 'interval', 'type_interval', 'startAt', 'finishAt', 'imgswitch'];

    public $timestamps = false;

    public static $validate = [
        'rules'=> [
            'name' => 'required',
           // 'type' => 'required',
            'message' => 'required',
        ],
        'messages'=> [
            'name.required' => 'Заполните поле Имя',
            //'type.required' => 'Заполните поле Тип',
            'message.required' => 'Заполните поле Сообщения',
        ]

    ];

    public function getPhotoAttribute() {
        $file = Fileentry::where('id', $this->imgswitch)->first();
        if($file) {
            $filepath = route('getentry', [$file->original_filename, 'notification']);
            return $filepath;
        }
        return null;
    }

    public function getPhotoPathAttribute() {
        $file = Fileentry::where('id', $this->imgswitch)->first();
        if($file) {
            $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
            $filepath = $storagePath.config("telegram_bot.path_files.notification") . $file->original_filename;
            return $filepath;
        }
        return null;
    }


//    public function files() {
//        $this->hasMany('BotTelegram\Models\Fileentry', 'id', 'object_id');
//    }
}