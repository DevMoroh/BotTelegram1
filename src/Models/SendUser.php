<?php

namespace BotTelegram\Models;

use Illuminate\Database\Eloquent\Model;

class SendUser extends Model
{
    protected $table = 'send_user';

    public $timestamps = false;

   // protected $appends = ['user', 'notification'];

    protected $fillable = [
        'user_service_id',
        'notification_id',
        'send_notice_id',
        'message'
    ];

    public function user() {
        return $this->belongsTo(UserService::class, 'user_service_id', 'external_id');
    }

    public function notification() {
        return $this->belongsTo(Notifications::class, 'notification_id', 'id');
    }
}
