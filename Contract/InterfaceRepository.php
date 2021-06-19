<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Contract;

use Illuminate\Http\Request;

/**
 * Interface CoreRepository
 * @package Modules\Core\Repositories
 */
interface InterfaceRepository
{
	/**
	 * [save description]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
    public function save(Request $request, $callback = null);

    /**
     * [validatePost description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function validatePost(Request $request);

    /**
     * Update a resource
     * @param  $model
     * @param  array $data
     * @return $model
     */
    public function setPostType($post_type);

    /**
     * Update a resource
     * @param  $model
     * @param  array $data
     * @return $model
     */
    public function getPostType();

    /**
     * [getCategory description]
     * @return [type] [description]
     */
    public function getCategory();

    /**
     * [getTag description]
     * @return [type] [description]
     */
    public function getTag();
}
