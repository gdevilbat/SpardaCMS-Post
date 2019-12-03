<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Foundation;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use Gdevilbat\SpardaCMS\Modules\Post\Contract\InterfacePost;
use Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\CoreController;

use Gdevilbat\SpardaCMS\Modules\Post\Entities\Post as Post_m;
use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms as Terms_m;
use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy as TermTaxonomy_m;
use Gdevilbat\SpardaCMS\Modules\Post\Entities\PostMeta as PostMeta_m;
use Gdevilbat\SpardaCMS\Modules\Post\Entities\TermRelationship as TermRelationship_m;
use Gdevilbat\SpardaCMS\Modules\Core\Repositories\Repository;

use DB;
use View;
use Auth;
use Storage;
use Validator;
use Carbon\Carbon;

/**
 * Class EloquentCoreRepository
 *
 * @package Gdevilbat\SpardaCMS\Modules\Core\Repositories\Eloquent
 */
abstract class AbstractPost extends CoreController implements InterfacePost
{
    public function __construct()
    {
        parent::__construct();
        $this->post_m = new Post_m;
        $this->post_repository = new Repository(new Post_m);
        $this->terms_m = new Terms_m;
        $this->terms_repository = new Repository(new Terms_m);
        $this->term_taxonomy_m = new TermTaxonomy_m;
        $this->term_taxonomy_repository = new Repository(new TermTaxonomy_m);
        $this->postmeta_m = new PostMeta_m;
        $this->postmeta_repository = new Repository(new PostMeta_m);
        $this->term_relationship_m = new TermRelationship_m;
        $this->term_relationship_repository = new Repository(new TermRelationship_m);
    }

    public function index()
    {
        return view($this->getModule().'::admin.'.$this->data['theme_cms']->value.'.content.'.ucfirst($this->getPostType()).'.master', $this->data);
    }

    public function serviceMaster(Request $request)
    {
        $column = $this->getColumnOrder();

        $length = !empty($request->input('length')) ? $request->input('length') : 10 ;
        $column = !empty($request->input('order.0.column')) ? $column[$request->input('order.0.column')] : \Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey() ;
        $dir = !empty($request->input('order.0.dir')) ? $request->input('order.0.dir') : 'DESC' ;
        $searchValue = $request->input('search')['value'];

        config()->set('database.connections.mysql.strict', false);
        \DB::reconnect();

        $query = $this->getQuerybuilder($column, $dir);

        $recordsTotal = $query->count();
        $filtered = $query;

        if($searchValue)
        {
            $filtered->where(function($query) use ($searchValue){
                        $query->where(DB::raw("CONCAT(post_title,'-',post_slug,'-',post_status,'-',".$this->post_m::getTableName().".created_at)"), 'like', '%'.$searchValue.'%')
                                ->orWhereHas('taxonomies.term', function($query) use ($searchValue){
                                    $query->where(DB::raw("CONCAT(name,'-',slug)"), 'like', '%'.$searchValue.'%');
                                })
                                ->orWhereHas('author', function($query) use ($searchValue){
                                    $query->where(DB::raw("CONCAT(name)"), 'like', '%'.$searchValue.'%');
                                });

                    });
        }

        $filteredTotal = $filtered->count();

        $this->data['length'] = $length;
        $this->data['column'] = $column;
        $this->data['dir'] = $dir;
        $this->data['posts'] = $this->getQueryleftJoinBuilder($filtered)->offset($request->input('start'))->limit($length)->get();

        config()->set('database.connections.mysql.strict', true);
        \DB::reconnect();

        $table =  $this->parsingDataTable($this->data['posts']);

        return ['data' => $table, 'draw' => (integer)$request->input('draw'), 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $filteredTotal];
    }

    public function getQuerybuilder($column, $dir)
    {
        $query = $this->post_m->orderBy($column, $dir);

        return $query;
    }

    public function getQueryleftJoinBuilder(\Illuminate\Database\Eloquent\Builder $post)
    {
        return $post->leftJoin(\Gdevilbat\SpardaCMS\Modules\Core\Entities\User::getTableName(), \Gdevilbat\SpardaCMS\Modules\Core\Entities\User::getTableName().'.id', '=', Post_m::getTableName().'.created_by')
                                ->leftJoin(TermRelationship_m::getTableName(), Post_m::getTableName().'.'.Post_m::getPrimaryKey(), '=', TermRelationship_m::getTableName().'.object_id')
                                ->leftJoin(TermTaxonomy_m::getTableName(), TermTaxonomy_m::getTableName().'.'.TermTaxonomy_m::getPrimaryKey(), '=', TermRelationship_m::getTableName().'.term_taxonomy_id')
                                ->leftJoin(Terms_m::getTableName(), Terms_m::getTableName().'.'.Terms_m::getPrimaryKey(), '=', TermTaxonomy_m::getTableName().'.term_id')
                                ->with('taxonomies.allTaxonomyParents.term', 'taxonomies.term')
                                ->where('post_type', $this->getPostType())
                                ->groupBy(Post_m::getTableName().'.'.Post_m::getPrimaryKey())
                                ->select(Post_m::getTableName().'.*', \Gdevilbat\SpardaCMS\Modules\Core\Entities\User::getTableName().'.name as author_name', Terms_m::getTableName().'.name as term_name');
    }

    public function getColumnOrder()
    {
        return [\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey(), 'post_title', \Gdevilbat\SpardaCMS\Modules\Core\Entities\User::getTableName().'.name', Terms_m::getTableName().'.name', 'tags','comment', 'post_status','created_at'];
    }

    public function parsingDataTable($posts)
    {
        /*=========================================
        =            Parsing Datatable            =
        =========================================*/
            
            $data = array();
            $i = 0;
            foreach ($posts as $key_post => $post) 
            {
                if(Auth::user()->can('read-'.$this->getModule(), $post))
                {
                    $data[$i][] = $post->getKey();
                    $data[$i][] = $post->post_title;
                    $data[$i][] = $post->author->name;

                    $categories = $post->taxonomies->where('taxonomy', $this->getCategory());
                    if($categories->count() > 0)
                    {
                        $data[$i][] = '';
                        foreach ($categories as $key => $category) 
                        {
                            $data[$i][count($data[$i]) - 1] .= $this->getCategoryHtmlTag($this->getPostCategory($category)).'</br>';
                        }
                    }
                    else
                    {
                        $data[$i][] = '-';
                    }

                    $tags = $post->taxonomies->where('taxonomy', 'tag');
                    if($tags->count() > 0)
                    {
                        $data[$i][] = '';
                        foreach ($tags as $key => $tag) 
                        {
                            $data[$i][count($data[$i]) - 1] .= '<span class="badge badge-danger mx-1">'.$tag->term->name.'</span>';
                        }
                    }
                    else
                    {
                        $data[$i][] = '-';
                    }

                    $data[$i][] = '';

                    if($post->post_status_bool)
                    {
                        $data[$i][] = '<a href="#" class="btn btn-success p-1">'.$post->post_status.'</a>';;
                    }
                    else
                    {
                        $data[$i][] = '<a href="#" class="btn btn-warning p-1">'.$post->post_status.'</a>';;
                    }

                    $data[$i][] = $post->created_at->toDateTimeString();
                    $data[$i][] = $this->getActionTable($post);
                    $i++;
                }
            }

            return $data;
        
        /*=====  End of Parsing Datatable  ======*/
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $this->data['method'] = method_field('POST');
        //$this->data['parents'] = $this->post_m->where('post_type', $this->getPostType())->get();
        $this->data['categories'] = $this->term_taxonomy_m->with('term')->where(['taxonomy' => $this->getCategory()])->get();
        $this->data['tags'] = $this->term_taxonomy_m->with('term')->where(['taxonomy' => $this->getTag()])->get();
        if(isset($_GET['code']))
        {
            $this->data['post'] = $this->post_repository->with(['postMeta', 'taxonomies'])->find(decrypt($_GET['code']));
            //$this->data['parents'] = $this->post_m->where('post_type', $this->getPostType())->where(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey(), '!=', decrypt($_GET['code']))->get();
            $this->data['method'] = method_field('PUT');
            $this->authorize('update-'.$this->getModule(), $this->data['post']);
        }

        return view($this->getModule().'::admin.'.$this->data['theme_cms']->value.'.content.'.ucfirst($this->getPostType()).'.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request, $callback = null)
    {
        $validator = $this->validatePost($request);

        if($request->isMethod('POST'))
        {
            $validator->addRules([
                'post.post_slug' => 'max:191|unique:'.$this->post_m->getTable().',post_slug'
            ]);
        }
        else
        {
            $validator->addRules([
                'post.post_slug' => 'max:191|unique:'.$this->post_m->getTable().',post_slug,'.decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey())).','.\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey()
            ]);
        }

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        if($request->isMethod('POST'))
        {
            $data = $request->except('_token', '_method', 'password_confirmation', 'role_id');
            $post = new $this->post_m;
        }
        else
        {
            $data = $request->except('_token', '_method', 'password_confirmation', 'role_id', \Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey());
            $post = $this->post_repository->findOrFail(decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey())));
            $this->authorize('update-'.$this->getModule(), $post);
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
                    $postmeta = $this->postmeta_m->where(['post_id' => $post->getKey(), 'meta_key' => $key])->first();
                    if(empty($postmeta))
                        $postmeta = new $this->postmeta_m;

                    if(!empty($value))
                    {
                        $postmeta->post_id = $post->getKey();
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
                    $postmeta = $this->postmeta_m->where(['post_id' => $post->getKey(), 'meta_key' => 'cover_image'])->first();

                    $path = null;

                    if($request->hasFile('meta.cover_image.file'))
                    {
                        $path = $request->file('meta.cover_image.file')->storeAs(Carbon::now()->format('Y/m'), $request->file('meta.cover_image.file')->getClientOriginalName());
                    }

                    if(empty($postmeta))
                    {
                        $postmeta = new $this->postmeta_m;
                    }
                    else
                    {
                        $tmp = $postmeta->meta_value['file'];
                        
                        if($tmp != $path && !empty($path))
                        {
                            Storage::delete($tmp);
                        }
                        else
                        {
                            $path = $tmp;
                        }

                    }


                    $cover_image['file'] = $path;

                    $postmeta->post_id = $post->getKey();
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
            

            if($request->isMethod('POST'))
            {
                return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Add '.ucfirst($this->getPostType()).'!'));
            }
            else
            {
                return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Update '.ucfirst($this->getPostType()).'!'));
            }
        }
        else
        {
            if($request->isMethod('POST'))
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Add '.ucfirst($this->getPostType()).'!'));
            }
            else
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Update '.ucfirst($this->getPostType()).'!'));
            }
        }

    }

    
    public function getActionTable($post)
    {
        $view = View::make($this->getModule().'::admin.'.$this->data['theme_cms']->value.'.content.'.ucfirst($this->getPostType()).'.service_master', [
            'post' => $post
        ]);

        $html = $view->render();
       
       return $html;
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request)
    {
        $query = $this->post_m->findOrFail(decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey())));
        $this->authorize('delete-'.$this->getModule(), $query);

        try {
            
            if($query->delete())
            {
                return redirect()->back()->with('global_message', array('status' => 200,'message' => 'Successfully Delete '.ucfirst($this->getPostType()).'!'));
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('global_message', array('status' => 200,'message' => 'Failed Delete Post, It\'s Has Been Used!'));
        }
    }

    public function validatePost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post.post_title' => 'required',
            'post.post_slug' => 'required|max:191',
            'meta.cover_image.file' => [
                    'max:500',
                     function ($attribute, $value, $fail) use ($request) {
                        if (Storage::exists('E-Paper/'.$request->file('meta.cover_image.file')->getClientOriginalName())) {
                            $fail($attribute.' Is Exist. Please Use Another Filename.');
                        }
                    },
                ]
        ]);

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

    public function getPostCategory($category, $text = [])
    {
        array_push($text, $category->term->name);

        if(!empty($category->allTaxonomyParents))
        {
           $text = $this->getPostCategory($category->parent, $text);
        }

        return $text;
    }

    public function getCategoryHtmlTag($categories)
    {
        $text = '';

        $categories = collect($categories)->reverse();

        foreach ($categories as $key => $value) 
        {
            $text .= '<span class="badge badge-danger mx-1">'.$value.'</span>';
        }

        return $text;
    }


    public function getModule()
    {
        return $this->module;
    }

    public function getPostType()
    {
        return $this->post_type;
    }
}
