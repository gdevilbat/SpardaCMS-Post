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
        if(empty($value))
        {
        	if(!empty($this->post_content))
        	{
	            $this->attributes['post_excerpt'] = substr($this->post_content, 0, 50).'[...]';
        	}
        }
    }
}
