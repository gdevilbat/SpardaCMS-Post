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

        $this->module = 'post';
        $this->post_type = 'post';
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
}
