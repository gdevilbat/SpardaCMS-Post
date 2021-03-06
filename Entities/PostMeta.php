<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Entities;

use Illuminate\Database\Eloquent\Model;

class PostMeta extends Model
{
    protected $fillable = [];
    protected $table = 'postmeta';
    protected $primaryKey = 'id_postmeta';
    protected $casts = [
        'meta_value' => 'array',
    ];

    public static function getTableName()
    {
        return with(new Static)->getTable();
    }

    public static function getPrimaryKey()
    {
        return with(new Static)->getKeyName();
    }
}
