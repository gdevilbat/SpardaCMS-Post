<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use DB;

use Gdevilbat\SpardaCMS\Modules\Core\Entities\Module;

class PostModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        Module::firstOrCreate(
            ['slug' => 'post'],
            [
                'name' => 'Post',
                'scope' => array('menu', 'create', 'read', 'update', 'delete'),
                'is_scanable' => '1',
                'created_at' => \Carbon\Carbon::now()
            ]
        );
    }
}
