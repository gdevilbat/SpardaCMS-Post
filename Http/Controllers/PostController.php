<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\CoreController;

use Gdevilbat\SpardaCMS\Modules\Post\Entities\Post as Post_m;
use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy as TermTaxonomy_m;
use Gdevilbat\SpardaCMS\Modules\Post\Entities\PostMeta as PostMeta_m;
use Gdevilbat\SpardaCMS\Modules\Post\Entities\TermRelationship as TermRelationship_m;
use Gdevilbat\SpardaCMS\Modules\Core\Repositories\Repository;

use Validator;
use DB;
use View;
use Auth;
use Storage;

class PostController extends CoreController
{
    protected $module = 'post';
    protected $post_type = 'post';
    protected $category = 'category';
    protected $tag = 'tag';

    public function __construct()
    {
        parent::__construct();
        $this->post_m = new Post_m;
        $this->post_repository = new Repository(new Post_m);
        $this->term_taxonomy_m = new TermTaxonomy_m;
        $this->term_taxonomy_repository = new Repository(new TermTaxonomy_m);
        $this->postmeta_m = new PostMeta_m;
        $this->postmeta_repository = new Repository(new PostMeta_m);
        $this->term_relationship_m = new TermRelationship_m;
        $this->term_relationship_repository = new Repository(new TermRelationship_m);
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view($this->module.'::admin.'.$this->data['theme_cms']->value.'.content.'.ucfirst($this->module).'.master', $this->data);
    }

    public function serviceMaster(Request $request)
    {
        $column = ['id', 'post_title', 'author', 'categories', 'tags','comment', 'created_at'];

        $length = !empty($request->input('length')) ? $request->input('length') : 10 ;
        $column = !empty($request->input('order.0.column')) ? $column[$request->input('order.0.column')] : 'id' ;
        $dir = !empty($request->input('order.0.dir')) ? $request->input('order.0.dir') : 'DESC' ;
        $searchValue = $request->input('search')['value'];

        $query = $this->post_m->with('taxonomies.term')
                                ->where('post_type', $this->post_type)
                                ->orderBy($column, $dir)
                                ->limit($length);

        $recordsTotal = $query->count();
        $filtered = $query;

        if($searchValue)
        {
            $filtered->where(function($query) use ($searchValue){
                        $query->where(DB::raw("CONCAT(post_title,'-',post_slug,'-',created_at)"), 'like', '%'.$searchValue.'%')
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
        $this->data['posts'] = $filtered->offset($request->input('start'))->limit($length)->get();

        /*=========================================
        =            Parsing Datatable            =
        =========================================*/
            
            $data = array();
            $i = 0;
            foreach ($this->data['posts'] as $key_post => $post) 
            {
                if(Auth::user()->can('read-'.$this->post_type, $post))
                {
                    $data[$i][0] = $post->id;
                    $data[$i][1] = $post->post_title;
                    $data[$i][2] = $post->author->name;

                    $categories = $post->taxonomies->where('taxonomy', $this->category);
                    if($categories->count() > 0)
                    {
                        $data[$i][3] = '';
                        foreach ($categories as $key => $category) 
                        {
                            $data[$i][3] .= '<span class="badge badge-danger mx-1">'.$category->term->name.'</span>';
                        }
                    }
                    else
                    {
                        $data[$i][3] = '-';
                    }

                    $tags = $post->taxonomies->where('taxonomy', 'tag');
                    if($tags->count() > 0)
                    {
                        $data[$i][4] = '';
                        foreach ($tags as $key => $tag) 
                        {
                            $data[$i][4] .= '<span class="badge badge-danger mx-1">'.$tag->term->name.'</span>';
                        }
                    }
                    else
                    {
                        $data[$i][4] = '-';
                    }

                    $data[$i][5] = '';
                    $data[$i][6] = $post->created_at->toDateTimeString();
                    $data[$i][7] = $this->getActionTable($post);
                    $i++;
                }
            }
        
        /*=====  End of Parsing Datatable  ======*/
        
        return ['data' => $data, 'draw' => (integer)$request->input('draw'), 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $filteredTotal];
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $this->data['method'] = method_field('POST');
        $this->data['parents'] = $this->post_m->where('post_type', $this->post_type)->get();
        $this->data['categories'] = $this->term_taxonomy_m->with('term')->where(['taxonomy' => $this->category])->get();
        $this->data['tags'] = $this->term_taxonomy_m->with('term')->where(['taxonomy' => $this->tag])->get();
        if(isset($_GET['code']))
        {
            $this->data['post'] = $this->post_repository->with(['postMeta', 'taxonomies'])->find(decrypt($_GET['code']));
            $this->data['parents'] = $this->post_m->where('post_type', $this->post_type)->where('id', '!=', decrypt($_GET['code']))->get();
            $this->data['method'] = method_field('PUT');
            $this->authorize('update-'.$this->post_type, $this->data['post']);
        }

        return view($this->module.'::admin.'.$this->data['theme_cms']->value.'.content.'.ucfirst($this->module).'.form', $this->data);
    }

    

    private function getActionTable($post)
    {
        $view = View::make($this->module.'::admin.'.$this->data['theme_cms']->value.'.content.'.ucfirst($this->module).'.service_master', [
            'post' => $post
        ]);

        $html = $view->render();
       
       return $html;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post.post_title' => 'required',
            'post.post_slug' => 'required|max:191',
            'meta.feature_image' => 'max:500'
        ]);

        if($request->isMethod('POST'))
        {
            $validator->addRules([
                'post.post_slug' => 'max:191|unique:'.$this->post_m->getTable().',post_slug'
            ]);
        }
        else
        {
            $validator->addRules([
                'post.post_slug' => 'max:191|unique:'.$this->post_m->getTable().',post_slug,'.decrypt($request->input('id')).',id'
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
            $data = $request->except('_token', '_method', 'password_confirmation', 'role_id', 'id');
            $post = $this->post_repository->findOrFail(decrypt($request->input('id')));
            $this->authorize('update-'.$this->post_type, $post);
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
        $post->post_type = $this->post_type;
        $post->modified_by = Auth::id();

        if($post->save())
        {
            /*==================================
            =            Meta Data Model       =
            ==================================*/

                $meta = $request->except('post', 'taxonomy','meta.feature_image', '_token', '_method', 'password_confirmation', 'role_id', 'id')['meta'];

                foreach ($meta as $key => $value) 
                {
                    $postmeta = $this->postmeta_m->where(['post_id' => $post->id, 'meta_key' => $key])->first();
                    if(empty($postmeta))
                        $postmeta = new $this->postmeta_m;

                    $postmeta->post_id = $post->id;
                    $postmeta->meta_key = $key;
                    $postmeta->meta_value = $value;
                    $postmeta->save();
                }

                if($request->hasFile('meta.feature_image'))
                {
                    $path = $request->file('meta.feature_image')->store('post/'.$post->post_slug,'public');

                    $postmeta = $this->postmeta_m->where(['post_id' => $post->id, 'meta_key' => 'feature_image'])->first();
                    if(empty($postmeta))
                    {
                        $postmeta = new $this->postmeta_m;
                    }
                    else
                    {
                        $tmp = $this->postmeta_m->where(['post_id' => $post->id, 'meta_key' => 'feature_image'])->first()->meta_value;
                        Storage::disk('public')->delete($tmp);
                    }

                    $postmeta->post_id = $post->id;
                    $postmeta->meta_key = 'feature_image';
                    $postmeta->meta_value = $path;
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
                        $category_data = $this->term_relationship_repository->getByAttributes(['object_id' => $post->id, 'term_taxonomy_id' => $value]);

                        if($category_data->count() == 0)
                        {
                            $category = new $this->term_relationship_m;
                            $category->term_taxonomy_id = $value;
                            $category->object_id = $post->id;
                            $category->save();
                        }

                    }
                }

                $self = $this;
                $data_category = $request->has('taxonomy.category') ? $request->input('taxonomy.category') : [];
                $remove_category_relation = $this->term_relationship_m->where('object_id', $post->id)
                                                               ->whereNotIn('term_taxonomy_id', $data_category)
                                                               ->whereHas('taxonomy', function($query) use ($self){
                                                                    $query->where('taxonomy', $self->category);
                                                               })
                                                               ->pluck('id');

               $this->term_relationship_m->whereIn('id', $remove_category_relation)->delete();
            
            /*=====  End of Category Relationship  ======*/

            /*=============================================
            =            Tag Relationship            =
            =============================================*/

                if($request->has('taxonomy.tag'))
                {
                    foreach ($request->input('taxonomy.tag') as $key => $value) 
                    {
                        $tag_data = $this->term_relationship_repository->getByAttributes(['object_id' => $post->id, 'term_taxonomy_id' => $value]);

                        if($tag_data->count() == 0)
                        {
                            $tag = new $this->term_relationship_m;
                            $tag->term_taxonomy_id = $value;
                            $tag->object_id = $post->id;
                            $tag->save();
                        }

                    }
                }

                $self = $this;
                $data_tag = $request->has('taxonomy.tag') ? $request->input('taxonomy.tag') : [];
                $remove_tag_relation = $this->term_relationship_m->where('object_id', $post->id)
                                                               ->whereNotIn('term_taxonomy_id', $data_tag)
                                                               ->whereHas('taxonomy', function($query) use ($self){
                                                                    $query->where('taxonomy', $self->tag);
                                                               })
                                                               ->pluck('id');

               $this->term_relationship_m->whereIn('id', $remove_tag_relation)->delete();
            
            /*=====  End of Tag Relationship  ======*/
            
            

            if($request->isMethod('POST'))
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\\'.ucfirst($this->module).'\Http\Controllers\\'.ucfirst($this->module).'Controller@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Add Post!'));
            }
            else
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\\'.ucfirst($this->module).'\Http\Controllers\\'.ucfirst($this->module).'Controller@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Update Post!'));
            }
        }
        else
        {
            if($request->isMethod('POST'))
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Add Post!'));
            }
            else
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Update Post!'));
            }
        }

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view($this->module.'::show');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request)
    {
        $query = $this->post_m->findOrFail(decrypt($request->input('id')));
        $this->authorize('delete-'.$this->post_type, $query);

        try {
            
            if($query->delete())
            {
                return redirect()->back()->with('global_message', array('status' => 200,'message' => 'Successfully Delete Post!'));
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('global_message', array('status' => 200,'message' => 'Failed Delete Post, It\'s Has Been Used!'));
        }
    }
}
