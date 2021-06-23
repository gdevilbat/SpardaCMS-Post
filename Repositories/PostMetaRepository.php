<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Repositories;

use Illuminate\Http\Request;
use Gdevilbat\SpardaCMS\Modules\Post\Entities\Post;

use Gdevilbat\SpardaCMS\Modules\Core\Repositories\AbstractRepository;
use Validator;

use ArrayObject;
use stdClass;

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
            return new SoftObject(json_decode(json_encode($row->meta_value)));
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

class SoftObject extends ArrayObject{
    private $obj;

    public function __construct($data) {
        if(is_object($data)){
            $this->obj = $data;
        }elseif(is_array($data)){
            // turn it into a multidimensional object
            $this->obj = json_decode(json_encode($data), false);
        }
    }

    public function __get($a) {
        if(isset($this->obj->$a)) {
            return $this->obj->$a;
        }else {
            // return an empty object in order to prevent errors with chain call
            $tmp = new stdClass();
            return new SoftObject($tmp);
        }
    }

    public function __set($key, $value) {
        $this->obj->$key = $value;
    }

    public function __call($method, $args) {
        call_user_func_array(Array($this->obj,$method),$args);
    }

    public function __toString() {
        return "";
    }
}
