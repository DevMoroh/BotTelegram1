<?php

namespace BotTelegram\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

date_default_timezone_set('Europe/Kiev');

class Notifications extends Model {

    protected $appends = ['photo', 'photo_path', 'counts'];
    public $guarded = ['id'];

    protected $table = 'notifications';

    protected $fillable = [
        'message',
        'type',
        'name',
        'status',
        'create_time',
        'start',
        'interval',
        'type_interval',
        'start_at',
        'status_send',
        'imgswitch',
    ];

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

    public function notes()
    {
        return $this->hasMany('BotTelegram\Models\SendNote','notification_id');
    }

    public function scopeCounts($query) {

        //SELECT * FROM (SELECT * FROM than_helped WHERE id_helped = '1' ORDER BY DATE DESC) t GROUP BY id_helped
        return DB::select("SELECT t.`counts` as `counts` FROM (SELECT * FROM `send_note` WHERE notification_id = {$this->id}
                           ORDER BY time_send DESC) as `t` GROUP BY notification_id");
//
//        return $query->leftJoin('send_note', 'notifications.id', '=', 'send_note.notification_id')
//            ->orderBy('time_send', 'desc')
//            ->groupBy('notifications.id');
    }
    
    public function getCountsAttribute() {
        if($this->status_send > 0) {
            $counts = self::counts();
            if ($counts) $r = $counts[0]->counts;
        }else{
            $r = 0;
        }
        return $r;
    }

    public function setStartAtAttribute($value) {
        if(!empty($value)) {
            $this->attributes['start_at'] = strtotime($value);
        }
    }

    public function getStartAtAttribute($value) {
        if(!empty($value)) {
             return date("Y-m-d H:i:s", $value);
        }
        return 'Не задано время';
    }

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


//    public function file() {
//        return $this->hasOne(Fileentry::class, 'object_id', 'imgswitch');
//    }
}