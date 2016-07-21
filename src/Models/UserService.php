<?php namespace BotTelegram\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserService extends Model {

    protected $primaryKey = 'id';
    protected $table = 'users_service';

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'external_id',
        'time_create',
        'service',
        'status',
        'letyshops_user_id',
        'hash_user',
        'chat',
        'subscribe'
    ];

    public $timestamps = false;

    public function getTimeCreateAttribute($value)
    {
        if (is_null($value))
            return null;
        else
            return Carbon::parse(date('d M Y H:i', $value))->format('d M Y H:i');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function($user)
        {
            $user->time_create = time();
        });

        static::updating(function($page)
        {
            // do stuff
        });


    }

    public function messages()
    {
        return $this->belongsTo('BotTelegram\Models\MessagesTelegram',  'external_id', 'from');
    }

}
