<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use DB;

class PostTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $faker = \Faker\Factory::create();

        $id = DB::table('posts')->insertGetId(
            [
                'post_title' => $faker->word,
                'post_slug' => str_slug($faker->word),
                'post_content' => $faker->text,
                'post_excerpt' => $faker->word,
                'post_status' => 'draft',
                'created_by' => 1,
                'modified_by' => 1,
                'created_at' => \Carbon\Carbon::now()
            ]
        );

        DB::table('term_relationships')->insert([
            [
                'term_taxonomy_id' => 1,
                'object_id' => $id,
                'created_at' => \Carbon\Carbon::now()
            ]
        ]);
    }
}
