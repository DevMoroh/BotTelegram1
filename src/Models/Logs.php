<?php

namespace BotTelegram\Models;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model {

    protected $fillable = ['time', 'info'];

    protected $table = 'logs';
}