<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Repositories;

use Illuminate\Http\Request;
use Gdevilbat\SpardaCMS\Modules\Post\Entities\Post;

use Gdevilbat\SpardaCMS\Modules\Core\Repositories\AbstractRepository;
use Validator;

/**
 * Class EloquentCoreRepository
 *
 * @package Gdevilbat\SpardaCMS\Modules\Core\Repositories\Eloquent
 */
class PostMetaRepository extends AbstractRepository
{
	public function __construct(\Gdevilbat\SpardaCMS\Modules\Post\Entities\PostMeta $model, \Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository $acl)
    {
        parent::__construct($model, $acl);
        $this->setModule('post');
    }

    public function getMeta($data)
    {
        $validator = Validator::make($data, [
            Post::FOREIGN_KEY => 'required',
            'meta_key' => 'required'
        ]);

        if($validator->fails())
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json($validator->errors(), 422));

        $row = $this->model->where(Post::FOREIGN_KEY, $data[Post::FOREIGN_KEY])
                    ->where('meta_key', $data['meta_key'])
                    ->first();

        if(!empty($row))
        {
            return $row->meta_value;
        }

        return '';
    }

    public function getMetaData($meta_key)
    {
        return $this->getMeta([
            Post::FOREIGN_KEY => $this->post->getKey(),
            'meta_key' => $meta_key
        ]);
    }
}
