<?php namespace BotTelegram\Traits;


use BotTelegram\Models\TagsModel;
use Illuminate\Support\Facades\DB;

trait TagsTrait{


    protected static $rel_table = 'tags_objects';

    protected static function boot () {
        parent::boot();
//
//        static::saved(function($model){
//
//            DB::table(self::$rel_table)->where('object_id', $model->id)->delete();
//            var_dump($model->tags_list);
//            foreach ($model->tags_list as $tag_id) {
//                $model->tags()->attach($tag_id);
//            }
//
//        });

    }

    public function tags() {

        return $this->belongsToMany(
            TagsModel::class,
            'tags_objects',
            'object_id',
            'tag_id'
        );
    }
}