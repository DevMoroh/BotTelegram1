<?php namespace BotTelegram\Models;

use Illuminate\Database\Eloquent\Model;

class UserService extends Model {


    protected $table = 'users_service';

    protected $fillable = ['first_name', 'last_name', 'external_id', 'time_create', 'service', 'status'];

    

}
