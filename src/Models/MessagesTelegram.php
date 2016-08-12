<?php namespace BotTelegram\Models;

use Illuminate\Database\Eloquent\Model;

class MessagesTelegram extends Model {

    protected $table = 'messages_telegram';

    protected $fillable = ['from', 'date', 'chat', 'message_id', 'text', 'type', 'command', 'approved', 'answer', 'message_answer_id'];

    public $timestamps = false;

    public function getFromAttribute($value)
    {
        $user = UserService::where(['external_id'=>$value])->first();
        return ($user) ? $user->first_name.' '.$user->last_name : '';
    }

    public function getDateAttribute($value) {
        return date('d M Y H:i', $value);
    }

    public function users() {
        return $this->hasOne('BotTelegram\Models\UserService', 'external_id', 'from');
    }
    
}