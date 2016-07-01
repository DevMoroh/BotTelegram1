<?php namespace BotTelegram\Models;

use Illuminate\Database\Eloquent\Model;

class MessagesTelegram extends Model {

    protected $table = 'messages_telegram';

    protected $fillable = ['from', 'date', 'chat', 'message_id', 'text', 'type', 'command'];

    public $timestamps = false;

}