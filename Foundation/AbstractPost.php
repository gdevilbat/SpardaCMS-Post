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

use DB;
use View;
use Auth;
use Storage;
use Validator;
use Carbon\Carbon;
use StorageService;

/**
 * Class EloquentCoreRepository
 *
 * @package Gdevilbat\SpardaCMS\Modules\Core\Repositories\Eloquent
 */
abstract class AbstractPost extends CoreController implements InterfacePost
{
    public function __construct(\Gdevilbat\SpardaCMS\Modules\Post\Contract\InterfaceRepository $post_repository)
    {
        parent::__construct();
        $this->post_repository = $post_repository;
    }

    public function index()
    {
        return view($this->post_repository->getModule().'::admin.'.$this->data['theme_cms']->value.'.content.'.ucfirst($this->post_repository->getPostType()).'.master', $this->data);
    }

    public function serviceMaster(Request $request)
    {
        $column = $this->getColumnOrder();

        $length = !empty($request->input('length')) ? $request->input('length') : 10 ;
        $column = $request->input('order.0.column') != null ? $column[$request->input('order.0.column')] : \Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey() ;
        $dir = !empty($request->input('order.0.dir')) ? $request->input('order.0.dir') : 'DESC' ;
        $searchValue = $request->input('search')['value'];

        if(!\App::environment('testing'))
        {
            config()->set('database.connections.mysql.strict', false);
            \DB::reconnect();
        }

        $query = $this->getQuerybuilder($column, $dir);

        $recordsTotal = $query->count();
        $filtered = $query;

        if($searchValue)
        {
            $filtered->where(function($query) use ($searchValue){
                        $query->where(DB::raw("CONCAT(post_title,'-',post_slug,'-',post_status,'-',".$this->post_m::getTableWithPrefix().".created_at)"), 'like', '%'.$searchValue.'%')
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

        if(!\App::environment('testing'))
        {
            config()->set('database.connections.mysql.strict', true);
            \DB::reconnect();
        }

        $table =  $this->parsingDataTable($this->data['posts']);

        return ['data' => $table, 'draw' => (integer)$request->input('draw'), 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $filteredTotal];
    }

    public function getQuerybuilder($column, $dir)
    {
        $query = $this->post_repository->buildQueryByCreatedUser(['post_type' => $this->post_repository->getPostType()])
                                ->orderBy($column, $dir);

        return $query;
    }

    public function getQueryleftJoinBuilder(\Illuminate\Database\Eloquent\Builder $post)
    {
        return $post->leftJoin(\Gdevilbat\SpardaCMS\Modules\Core\Entities\User::getTableName(), \Gdevilbat\SpardaCMS\Modules\Core\Entities\User::getTableName().'.id', '=', Post_m::getTableName().'.created_by')
                                ->leftJoin(TermRelationship_m::getTableName(), Post_m::getTableName().'.'.Post_m::getPrimaryKey(), '=', TermRelationship_m::getTableName().'.object_id')
                                ->leftJoin(TermTaxonomy_m::getTableName(), TermTaxonomy_m::getTableName().'.'.TermTaxonomy_m::getPrimaryKey(), '=', TermRelationship_m::getTableName().'.term_taxonomy_id')
                                ->leftJoin(Terms_m::getTableName(), Terms_m::getTableName().'.'.Terms_m::getPrimaryKey(), '=', TermTaxonomy_m::getTableName().'.term_id')
                                ->with('taxonomies.allTaxonomyParents.term', 'taxonomies.term')
                                ->where('post_type', $this->post_repository->getPostType())
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
                $data[$i][] = $post->getKey();
                $data[$i][] = $post->post_title;
                $data[$i][] = $post->author->name;

                $categories = $post->taxonomies->where('taxonomy', $this->post_repository->getCategory());
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
        //$this->data['parents'] = $this->post_m->where('post_type', $this->post_repository->getPostType())->get();
        $this->data['categories'] = $this->post_repository->term_taxonomy_m->with('term')->where(['taxonomy' => $this->post_repository->getCategory()])->get();
        $this->data['tags'] = $this->post_repository->term_taxonomy_m->with('term')->where(['taxonomy' => $this->post_repository->getTag()])->get();
        if(isset($_GET['code']))
        {
            $this->data['post'] = $this->post_repository->with(['postMeta', 'taxonomies'])->find(decrypt($_GET['code']));
            //$this->data['parents'] = $this->post_m->where('post_type', $this->post_repository->getPostType())->where(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey(), '!=', decrypt($_GET['code']))->get();
            $this->data['method'] = method_field('PUT');
            $this->authorize('update-'.$this->post_repository->getModule(), $this->data['post']);
        }

        return view($this->post_repository->getModule().'::admin.'.$this->data['theme_cms']->value.'.content.'.ucfirst($this->post_repository->getPostType()).'.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $post = $this->post_repository->save($request);

        if($post->status)
        {
            if($request->isMethod('POST'))
            {
                return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Add '.ucfirst($this->post_repository->getPostType()).'!'));
            }
            else
            {
                return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Update '.ucfirst($this->post_repository->getPostType()).'!'));
            }
        }
        else
        {
            if($request->isMethod('POST'))
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Add '.ucfirst($this->post_repository->getPostType()).'!'));
            }
            else
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Update '.ucfirst($this->post_repository->getPostType()).'!'));
            }
        }

    }

    
    public function getActionTable($post)
    {
        $view = View::make($this->post_repository->getModule().'::admin.'.$this->data['theme_cms']->value.'.content.'.ucfirst($this->post_repository->getPostType()).'.service_master', [
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
        try {
                $query = $this->post_m->findOrFail(decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey())));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 400,'message' => 'Token Invalid, Try Again'));
        }

        $this->authorize('delete-'.$this->post_repository->getModule(), $query);

        if($query->post_status == 'publish')
            return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 400,'message' => 'Failed Delete Post, It\'s Has Been Published!'));

        try {
            
            if($query->delete())
            {
                return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Delete '.ucfirst($this->post_repository->getPostType()).'!'));
            }
            
        } catch (\Exception $e) {
            return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 400,'message' => 'Failed Delete Post, It\'s Has Been Used!'));
        }
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
}
