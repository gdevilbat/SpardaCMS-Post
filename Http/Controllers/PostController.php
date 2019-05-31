<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\CoreController;

use Gdevilbat\SpardaCMS\Modules\Post\Entities\Post as Post_m;
use Gdevilbat\SpardaCMS\Modules\Post\Entities\PostMeta as PostMeta_m;
use Gdevilbat\SpardaCMS\Modules\Core\Repositories\Repository;

use Validator;
use DB;
use View;
use Auth;

class PostController extends CoreController
{
    protected $post_type = 'post';

    public function __construct()
    {
        parent::__construct();
        $this->post_m = new Post_m;
        $this->post_repository = new Repository(new Post_m);
        $this->postmeta_m = new PostMeta_m;
        $this->postmeta_repository = new Repository(new PostMeta_m);
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('post::admin.'.$this->data['theme_cms']->value.'.content.master', $this->data);
    }

    public function serviceMaster(Request $request)
    {
        $column = ['id', 'post_name', 'author', 'categoris', 'tags','comment', 'created_at'];

        $length = !empty($request->input('length')) ? $request->input('length') : 10 ;
        $column = !empty($request->input('order.0.column')) ? $column[$request->input('order.0.column')] : 'id' ;
        $dir = !empty($request->input('order.0.dir')) ? $request->input('order.0.dir') : 'DESC' ;
        $searchValue = $request->input('search')['value'];

        $query = $this->post_m
                                ->orderBy($column, $dir)
                                ->limit($length);

        $recordsTotal = $query->count();
        $filtered = $query;

        if($searchValue)
        {
            $filtered->where(DB::raw("CONCAT(post_title,'-',post_title)"), 'like', '%'.$searchValue.'%');
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
                if(Auth::user()->can('read-post', $post))
                {
                    $data[$i][0] = $post->id;
                    $data[$i][1] = $post->post_title;
                    $data[$i][2] = '';
                    $data[$i][3] = '';
                    $data[$i][4] = '';
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
        if(isset($_GET['code']))
        {
            $this->data['post'] = $this->post_repository->with('postMeta')->find(decrypt($_GET['code']));
            $this->data['parents'] = $this->post_m->where('post_type', $this->post_type)->where('id', '!=', decrypt($_GET['code']))->get();
            $this->data['method'] = method_field('PUT');
            $this->authorize('update-post', $this->data['post']);
        }

        return view('post::admin.'.$this->data['theme_cms']->value.'.content.form', $this->data);
    }

    

    private function getActionTable($post)
    {
        $view = View::make('post::admin.'.$this->data['theme_cms']->value.'.content.service_master', [
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
            $this->authorize('update-post', $post);
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
            foreach ($data['meta'] as $key => $value) 
            {
                $postmeta = $this->postmeta_m->where(['post_id' => $post->id, 'meta_key' => $key])->first();
                if(empty($postmeta))
                    $postmeta = new $this->postmeta_m;

                $postmeta->post_id = $post->id;
                $postmeta->meta_key = $key;
                $postmeta->meta_value = $value;
                $postmeta->save();
            }

            if($request->isMethod('POST'))
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\PostController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Add Post!'));
            }
            else
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\PostController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Update Post!'));
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
        return view('post::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('post::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
