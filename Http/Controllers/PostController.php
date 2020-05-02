<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Post\Foundation\AbstractPost;

class PostController extends AbstractPost
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function __construct(\Gdevilbat\SpardaCMS\Modules\Post\Repositories\PostRepository $post_repository)
    {
        parent::__construct($post_repository);

        $this->setModule('post');
        $this->setPostType('post');
        $this->post_repository->setModule($this->getModule());
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view($this->getModule().'::show');
    }

    public function getCategory()
    {
        return 'category';
    }

    public function getTag()
    {
        return 'tag';
    }

    public function browsePostList()
    {
        $this->data['posts'] = $this->post_repository->buildQueryByAttributes(['post_status' => 'publish'], 'created_at', 'DESC')
                                    ->select([\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey(), 'post_title', 'created_at'])
                                    ->get();

        return view($this->getModule().'::admin.'.$this->data['theme_cms']->value.'.content.'.ucfirst($this->getPostType()).'.browse-post-list', $this->data);
    }

    public function getShortCodePost(Request $request)
    {
        return response()->json($this->post_m->where(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey(), $request->input('id'))->firstOrFail()->post_url);
    }
}
