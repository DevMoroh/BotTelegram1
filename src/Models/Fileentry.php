<?php namespace BotTelegram\Models;

use Illuminate\Database\Eloquent\Model;

class Fileentry extends Model {

    protected $table = "fileentries";
    protected $fillable = ['filename', 'mime', 'orginal_filename', 'type', 'object_id'];

}
