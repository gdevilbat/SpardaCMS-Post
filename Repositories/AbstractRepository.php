<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Repositories;

use Gdevilbat\SpardaCMS\Modules\Core\Repositories\AbstractRepository as AbstractRepository_m;
use Gdevilbat\SpardaCMS\Modules\Post\Contract\InterfaceRepository;
use Gdevilbat\SpardaCMS\Modules\Post\Entities\Post as Post_m;
use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms as Terms_m;
use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy as TermTaxonomy_m;
use Gdevilbat\SpardaCMS\Modules\Post\Entities\PostMeta as PostMeta_m;
use Gdevilbat\SpardaCMS\Modules\Post\Entities\TermRelationship as TermRelationship_m;
use Gdevilbat\SpardaCMS\Modules\Core\Repositories\Repository;

use Illuminate\Http\Request;

use Validator;
use StorageService;
use Storage;
use Auth;
use Str;
use Arr;
use Carbon\Carbon;

/**
 * Class EloquentCoreRepository
 *
 * @package Gdevilbat\SpardaCMS\Modules\Core\Repositories\Eloquent
 */
abstract class AbstractRepository extends AbstractRepository_m implements InterfaceRepository
{
    public function __construct(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post $model, \Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository $acl)
    {
        $this->model = $model;
        $this->acl = $acl;
        $this->terms_m = new Terms_m;
        $this->terms_repository = new Repository(new Terms_m, resolve(\Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository::class));
        $this->term_taxonomy_m = new TermTaxonomy_m;
        $this->term_taxonomy_repository = new Repository(new TermTaxonomy_m, resolve(\Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository::class));
        $this->postmeta_m = new PostMeta_m;
        $this->postmeta_repository = new Repository(new PostMeta_m, resolve(\Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository::class));
        $this->term_relationship_m = new TermRelationship_m;
        $this->term_relationship_repository = new Repository(new TermRelationship_m, resolve(\Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository::class));
    }

	public function validatePost(Request $request)
	{
		$validator = Validator::make($request->all(), [
            'post.post_title' => 'required',
            'post.post_slug' => 'required|max:191',
        ]);

         $validator->addRules([
            'meta.cover_image.file' => [
                'mimetypes:image/*'
            ]
        ]);

        if(!StorageService::isOriginalImageCompress())
        {
            $validator->addRules([
                'meta.cover_image.file' => [
                    'max:'.(string)config('storage-service.thumbnail.resolution.original.max_size')
                ]
            ]);
        }

        if(!empty($request->input('taxonomy.tag')))
        {
            $validator->addRules([
                    'taxonomy.tag' => [
                        function ($attribute, $value, $fail) use ($request) {
                            foreach ($value as $key => $val) {
                                if ($val > 191) {
                                    $fail($attribute.' . Tag Text Cannot Be Longer Than 191');
                                }
                            }
                        },
                ],      
            ]);
        }

        return $validator;
	}

	public function save(Request $request, $callback = null)
	{
		$validator = $this->validatePost($request);

        if($request->isMethod('POST'))
        {
            $validator->addRules([
                'post.post_slug' => 'max:191|unique:'.$this->model->getTable().',post_slug'
            ]);
        }
        else
        {
            $validator->addRules([
                'post.post_slug' => 'max:191|unique:'.$this->model->getTable().',post_slug,'.decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey())).','.\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey()
            ]);
        }

        $validator->validate();

        if($request->isMethod('POST'))
        {
            $data = $request->except('_token', '_method', 'password_confirmation', 'role_id');
            $post = new $this->model;
        }
        else
        {
            $data = $request->except('_token', '_method', 'password_confirmation', 'role_id', \Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey());
            $post = $this->findOrFail(decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey())));
            \Auth::user()->can('update-'.$this->getModule(), $post);
        }

        foreach ($data['post'] as $key => $value) 
        {
            $post->$key = $value;
        }

        if($request->isMethod('POST'))
        {
            $post->created_by = Auth::id();
        }

        $post->post_status = $request->has('post.post_status') ? $request->input('post.post_status') : '';
        $post->comment_status = $request->has('post.comment_status') ? $request->input('post.comment_status') : '';
        $post->post_type = $this->getPostType();
        $post->modified_by = Auth::id();

        if($post->save())
        {
        	/*==================================
            =            Meta Data Model       =
            ==================================*/

                $meta = [];

                $request_meta = $request->except('post', 'taxonomy','meta.cover_image', '_token', '_method', 'password_confirmation', 'role_id', \Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey());

                if(array_key_exists('meta', $request_meta))
                {
                    $meta = $request_meta['meta'];
                }


                foreach ($meta as $key => $value) 
                {
                    $postmeta = $this->postmeta_m->where([Post_m::FOREIGN_KEY => $post->getKey(), 'meta_key' => $key])->first();
                    if(empty($postmeta))
                        $postmeta = new $this->postmeta_m;

                    if(!empty($value))
                    {
                        $postmeta[Post_m::FOREIGN_KEY] = $post->getKey();
                        $postmeta->meta_key = $key;
                        $postmeta->meta_value = $value;
                        $postmeta->save();
                    }
                    else
                    {
                        $postmeta->delete();
                    }
                }

                if($request->has('meta.cover_image'))
                {
                    $cover_image = $request->input('meta.cover_image');

                    $postmeta = $this->postmeta_m->where([Post_m::FOREIGN_KEY => $post->getKey(), 'meta_key' => 'cover_image'])->first();

                    $path = json_decode(json_encode([]));

                    if($request->hasFile('meta.cover_image.file'))
                    {
                        $file = $request->file('meta.cover_image.file')->getClientOriginalName();
                        $filename = pathinfo($file, PATHINFO_FILENAME);
                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                        $path = StorageService::putImageAs(Carbon::now()->format('Y/m'), $request->file('meta.cover_image.file'), Str::slug(md5(microtime()).'-'.$filename, '-').'.'.$extension, true);
                    }

                    if(empty($postmeta))
                    {
                        $postmeta = new $this->postmeta_m;
                    }

                    $tmp = (!empty($postmeta->meta_value) && array_key_exists('file', $postmeta->meta_value)) ? $postmeta->meta_value['file'] : '';

                    if(!empty($path->file) && $tmp != $path->file)
                    {
                        Storage::delete($tmp);
                        $file = $path->file;
                    }
                    else
                    {
                        $file = $tmp;
                    }

                    $tmp = (!empty($postmeta->meta_value) && array_key_exists('small', $postmeta->meta_value)) ? $postmeta->meta_value['small'] : '';
                    
                    if(!empty($path->small) && $tmp != $path->small)
                    {
                        Storage::delete($tmp);
                        $small = $path->small;
                    }
                    else
                    {
                        $small = $tmp;
                    }

                    $tmp = (!empty($postmeta->meta_value) && array_key_exists('thumb', $postmeta->meta_value)) ? $postmeta->meta_value['thumb'] : '';
                    
                    if(!empty($path->thumb) && $tmp != $path->thumb)
                    {
                        Storage::delete($tmp);
                        $thumb = $path->thumb;
                    }
                    else
                    {
                        $thumb = $tmp;
                    }

                    $tmp = (!empty($postmeta->meta_value) && array_key_exists('medium', $postmeta->meta_value)) ? $postmeta->meta_value['medium'] : '';
                    
                    if(!empty($path->medium) && $tmp != $path->medium)
                    {
                        Storage::delete($tmp);
                        $medium = $path->medium;
                    }
                    else
                    {
                        $medium = $tmp;
                    }


                    $cover_image['file'] = $file;
                    $cover_image['small'] = $small;
                    $cover_image['thumb'] = $thumb;
                    $cover_image['medium'] = $medium;

                    $postmeta[Post_m::FOREIGN_KEY] = $post->getKey();
                    $postmeta->meta_key = 'cover_image';
                    $postmeta->meta_value = $cover_image;

                    $postmeta->save();
                }
            
            /*=====  End of Meta Data   ======*/

            /*=============================================
            =            Category Relationship            =
            =============================================*/

                if($request->has('taxonomy.category'))
                {
                    foreach ($request->input('taxonomy.category') as $key => $value) 
                    {
                        $category_data = $this->term_relationship_repository->getByAttributes(['object_id' => $post->getKey(), 'term_taxonomy_id' => $value]);

                        if($category_data->count() == 0)
                        {
                            $category = new $this->term_relationship_m;
                            $category->term_taxonomy_id = $value;
                            $category->object_id = $post->getKey();
                            $category->save();
                        }

                    }
                }

                $self = $this;
                $data_category = $request->has('taxonomy.category') ? $request->input('taxonomy.category') : [];
                $remove_category_relation = $this->term_relationship_m->where('object_id', $post->getKey())
                                                               ->whereNotIn('term_taxonomy_id', $data_category)
                                                               ->whereHas('taxonomy', function($query) use ($self){
                                                                    $query->where('taxonomy', $self->getCategory());
                                                               })
                                                               ->pluck(\Gdevilbat\SpardaCMS\Modules\Post\Entities\TermRelationship::getPrimaryKey());

               $this->term_relationship_m->whereIn(\Gdevilbat\SpardaCMS\Modules\Post\Entities\TermRelationship::getPrimaryKey(), $remove_category_relation)->delete();
            
            /*=====  End of Category Relationship  ======*/

            /*=============================================
            =            Tag Relationship            =
            =============================================*/

                $tmp_taxonomy_id = [];

                if($request->has('taxonomy.tag'))
                {
                    
                    foreach ($request->input('taxonomy.tag') as $key => $value) 
                    {
                        
                        /*----------  Create Terms First  ----------*/
                        $term = $this->terms_repository->findBySlug(Str::slug($value, '-'));

                        if(empty($term))
                        {
                            $term = new $this->terms_m;
                            $term->name = $value;
                            $term->slug = Str::slug($value, '-');
                            $term->created_by = Auth::id();
                            $term->modified_by = Auth::id();
                            $term->save();
                        }


                        /*----------  Create Tag Taxonomy  ----------*/
                        $taxonomy = TermTaxonomy_m::where(['term_id' => $term->getKey(), 'taxonomy' => 'tag'])->first();

                        if(empty($taxonomy))
                        {
                            $taxonomy = new $this->term_taxonomy_m;
                            $taxonomy->created_by = Auth::id();
                            $taxonomy->modified_by = Auth::id();
                            $taxonomy->taxonomy = 'tag';
                            $taxonomy->term_id = $term->getKey();
                            $taxonomy->save();
                        }

                        $tmp_taxonomy_id = Arr::prepend($tmp_taxonomy_id, $taxonomy->getKey());


                        /*----------  Create Tag Relationship  ----------*/
                        $tag_data = $this->term_relationship_repository->getByAttributes(['object_id' => $post->getKey(), 'term_taxonomy_id' => $taxonomy->getKey()]);

                        if($tag_data->count() == 0)
                        {
                            $tag = new $this->term_relationship_m;
                            $tag->term_taxonomy_id = $taxonomy->getKey();
                            $tag->object_id = $post->getKey();
                            $tag->save();
                        }

                    }
                }

                $self = $this;
                $remove_tag_relation = $this->term_relationship_m->where('object_id', $post->getKey())
                                                               ->whereNotIn('term_taxonomy_id', $tmp_taxonomy_id)
                                                               ->whereHas('taxonomy', function($query) use ($self){
                                                                    $query->where('taxonomy', $self->getTag());
                                                               })
                                                               ->pluck(\Gdevilbat\SpardaCMS\Modules\Post\Entities\TermRelationship::getPrimaryKey());

               $this->term_relationship_m->whereIn(\Gdevilbat\SpardaCMS\Modules\Post\Entities\TermRelationship::getPrimaryKey(), $remove_tag_relation)->delete();
            
            /*=====  End of Tag Relationship  ======*/

            /*==================================================
            =            Callback Action After Post            =
            ==================================================*/

                if(!empty($callback))
                {
                    call_user_func_array(array($this, $callback), array($request, $post));
                }
            
            /*=====  End of Callback Action After Post  ======*/

            return (object) [
        		'status' => true,
        		'data' => $post
        	];
        }
        else
        {
        	return (object) [
        		'status' => false
        	];
        }
	}

    public function setPostType($post_type)
    {
        $this->post_type = $post_type;
    }

    public function getPostType()
    {
        return $this->post_type;
    }
}
