<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Contract;

use Illuminate\Http\Request;

/**
 * Interface CoreRepository
 * @package Modules\Core\Repositories
 */
interface InterfacePost
{
    /**
     * @param  int $id
     * @return $model
     */
    public function index();

    /**
     * Return a collection of all elements of the resource
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function serviceMaster(Request $request);

    /**
     * @return Builder
     */
    public function create();

    /**
     * Update a resource
     * @param  $model
     * @param  array $data
     * @return $model
     */
    public function validatePost(Request $request);

    /**
     * Paginate the model to $perPage items per page
     * @param  int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function store(Request $request, callable $callback);

    /**
     * Create a resource
     * @param  $data
     * @return $model
     */
    public function destroy(Request $request);

    /**
     * Update a resource
     * @param  $model
     * @param  array $data
     * @return $model
     */
    public function getModule();

    /**
     * Update a resource
     * @param  $model
     * @param  array $data
     * @return $model
     */
    public function getPostType();

    /**
     * Update a resource
     * @param  $model
     * @param  array $data
     * @return $model
     */
    public function getActionTable($post);
}
