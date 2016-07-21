<?php

namespace BotTelegram\Models;
use BotTelegram\Traits\TagsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommandsTelegram extends Model {

    use TagsTrait;

    protected $table = 'commands_telegram';

    protected $appends = ['tags_list'];

    public $guarded = ['id'];

    protected $fillable = ['message', 'type', 'name', 'status', 'create_time'];

    public $timestamps = false;

    public static $validate = [
        'rules'=> [
            'name' => 'required',
            'type' => 'required',
            'message' => 'required',
        ],
        'messages'=> [
            'name.required' => 'Заполните поле Имя',
            'type.required' => 'Заполните поле Тип',
            'message.required' => 'Заполните поле Сообщения',
        ]
    ];
    
    public function getTagsListAttribute() {
        return $this->tags()->lists('tag_id');
    }

    public function setTagsListAttribute($tag_ids) {

        DB::table(self::$rel_table)->where('object_id', $this->id)->delete();
        if($tag_ids) {
            $tag_ids = explode(',', $tag_ids);
            $this->tags()->sync($tag_ids);
        }
    }

    public function subcommands() {
       return $this->hasMany('BotTelegram\Models\Subcommand', 'id', 'command_id');
    }

}