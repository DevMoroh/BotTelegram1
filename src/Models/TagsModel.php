<?php

namespace BotTelegram\Models;

use Illuminate\Database\Eloquent\Model;

class TagsModel extends Model
{

    protected $table = 'tags';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $fillable = [
        'name',
        'frequency'
    ];
}
