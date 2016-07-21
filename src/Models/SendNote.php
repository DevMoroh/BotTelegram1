<?php

namespace BotTelegram\Models;

use Illuminate\Database\Eloquent\Model;

class SendNote extends Model
{
    public $guarded = ['id'];

    public $timestamps = false;

    protected $table = 'send_note';

    protected $appends = ['notification'];

    protected $fillable = [
        'notification_id',
        'user_id',
        'status_send',
        'time_send',
        'counts'
    ];
//
//    public static function boot()
//    {
//        parent::boot();
//
//        static::creating(function ($note) {
//            $note->time_send = time();
//            //return true;
//            echo var_dump($note->time_send);
//        });
//
//        static::updating(function($note)
//        {
//            // do stuff
//        });
//    }

//    public function setTimeSendAttribute($value) {
//
//        $date = new \DateTime(null, new \DateTimeZone('Europe/Kiev') );
//        $this->attributes['time_send'] = $date->getTimestamp();
//    }

    public function getNotificationAttribute() {
        $n = $this->notificationsOne;

        return $n->name;
    }

    public function getTimeSendAttribute($value) {
        if(is_numeric($value)) {
            return date("Y-m-d H:i:s", $value);
        }
        return 'Не задано время';
    }

    public function notificationsOne(){
        return $this->hasOne('BotTelegram\Models\Notifications', 'id', 'notification_id');
    }

}
