<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use DB;

use Gdevilbat\SpardaCMS\Modules\Post\Entities\Post;
use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermRelationships;

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

        $post = Post::firstOrCreate(
            ['post_slug' => \Str::slug($faker->word)],
            [
                'post_title' => $faker->word,
                'post_content' => $faker->text,
                'post_excerpt' => $faker->word,
                'post_status' => 'draft',
                'created_by' => 1,
                'modified_by' => 1,
                'created_at' => \Carbon\Carbon::now()
            ]
        );

        TermRelationships::firstOrCreate(
            [
                'term_taxonomy_id' => 1,
                'object_id' => $post->getKey(),
            ],
            [
                'created_at' => \Carbon\Carbon::now()
            ]
        );
    }
}
