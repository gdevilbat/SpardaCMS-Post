<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Entities;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [];
    protected $table = 'posts';
    protected $primaryKey = 'id_posts';

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

    public function getGalleriesAttribute()
    {
        if(!empty($this->postMeta->where('meta_key', 'gallery')->first()))
            return json_decode(json_encode($this->postMeta->where('meta_key', 'gallery')->first()->meta_value));

        return [];
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

    public function getPostURLAttribute()
    {
        return url($this->created_at->format('Y').'/'.$this->created_at->format('m').'/'.$this->post_slug.'.html');
    }

    public function getPostStatusBoolAttribute()
    {
        if($this->post_status == 'publish')
            return true;

        return false;
    }

    public function scopeFilterTags($query, \Illuminate\Http\Request $request)
    {
        if(count($request->input()) > 0)
        {
            if(!empty($request->input('tags')))
            {
                $tags = $request->input('tags');

                foreach ($tags as $tag) 
                {
                    $query = $query->whereHas('taxonomies', function($query) use ($tag){
                                $query->where(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey(), $tag);
                            });
                }
            }
        }

        return $query;
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
