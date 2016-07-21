<?php

namespace BotTelegram\Models;

use Illuminate\Database\Eloquent\Model;

class Subcommand extends Model
{

    protected $table = 'subcommands';

    protected $fillable = [
        'command_id',
        'message',
        'type',
        'command',
    ];

}
