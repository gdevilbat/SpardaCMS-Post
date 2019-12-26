<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Repositories;

use Gdevilbat\SpardaCMS\Modules\Core\Repositories;
use Gdevilbat\SpardaCMS\Modules\Core\Repositories\AbstractRepository;

/**
 * Class EloquentCoreRepository
 *
 * @package Gdevilbat\SpardaCMS\Modules\Core\Repositories\Eloquent
 */
class PostRepository extends AbstractRepository
{
	public function __construct(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post $model)
    {
        $this->model = $model;
    }
}
