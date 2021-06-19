<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Repositories;

use Illuminate\Http\Request;

/**
 * Class EloquentCoreRepository
 *
 * @package Gdevilbat\SpardaCMS\Modules\Core\Repositories\Eloquent
 */
class PostRepository extends AbstractRepository
{
	public function __construct(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post $model, \Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository $acl)
    {
        parent::__construct($model, $acl);
        $this->setModule('post');
        $this->setPostType('post');
    }

    public function validatePost(Request $request)
    {
        $validator = parent::validatePost($request);

        $validator->addRules([
                'taxonomy.category' => 'required',
        ]);

        return $validator;
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
