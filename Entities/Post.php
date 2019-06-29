<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Entities;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [];
    protected $table = 'posts';

    public function postMeta()
    {
    	return $this->hasMany('Gdevilbat\SpardaCMS\Modules\Post\Entities\PostMeta', 'post_id');
    }

    public function taxonomies()
    {
    	return $this->belongsToMany('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy', 'term_relationships', 'object_id','term_taxonomy_id');
    }

    public function author()
    {
        return $this->belongsTo('\Gdevilbat\SpardaCMS\Modules\Core\Entities\User', 'created_by');
    }

    /**
     * Set the Post Status.
     *
     * @param  string  $value
     * @return void
     */
    public function setPostStatusAttribute($value)
    {
        if(!empty($value))
        {
            $this->attributes['post_status'] = 'publish';
        }
        else
        {
            $this->attributes['post_status'] = 'draft';
        }
    }

    /**
     * Set the Comment Post.
     *
     * @param  string  $value
     * @return void
     */
    public function setCommentStatusAttribute($value)
    {
        if(!empty($value))
        {
            $this->attributes['comment_status'] = 'open';
        }
        else
        {
            $this->attributes['comment_status'] = 'close';
        }
    }

    /**
     * Set the Post Excerpt.
     *
     * @param  string  $value
     * @return void
     */
    public function setPostExcerptAttribute($value)
    {
    	if(!empty($this->post_content))
    	{
            $this->attributes['post_excerpt'] = substr(strip_tags($this->post_content), 0, 50).'[...]';
    	}
    }

    public static function getTableName()
    {
        return with(new Static)->getTable();
    }

    public static function getPrimaryKey()
    {
        return with(new Static)->getKeyName();
    }
}
