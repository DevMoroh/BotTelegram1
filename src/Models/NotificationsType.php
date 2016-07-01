<?php

namespace BotTelegram\Models;
use Illuminate\Database\Eloquent\Model;

class NotificationsType extends Model {


    protected $table = 'notifications_type';

    protected $fillable = ['name', 'status'];

    public $timestamps = false;

}