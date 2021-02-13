<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PostTest extends DuskTestCase
{
    use DatabaseMigrations, \Gdevilbat\SpardaCMS\Modules\Core\Tests\ManualRegisterProvider;
    
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testCreatePost()
    {
        $user = \App\User::find(1);
        $faker = \Faker\Factory::create();

        $this->browse(function (Browser $browser) use ($user, $faker) {
            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\PostController@index'))
                    ->assertSee('Master Data of Post')
                    ->clickLink('Add New Post')
                    ->waitForText('Post Form')
                    ->AssertSee('Post Form')
                    ->type('post[post_title]', $faker->name)
                    ->type('post[post_slug]', $faker->name)
                    ->type('meta[meta_title]', $faker->name)
                    ->type('meta[meta_description]', $faker->text);

            $browser->script('document.getElementsByName("post[post_content]")[0].value = "'.$faker->text.'"');
            $browser->script('document.getElementsByName("post[post_status]")[0].checked = true');
            $browser->script('document.getElementsByName("post[comment_status]")[0].checked = true');
            //$browser->script('document.getElementsByName("post[post_parent]")[0].selectedIndex = 1'); Disable Post Parent For A while
            $browser->script('document.getElementsByName("taxonomy[category][]")[0].selectedIndex = 1');
            $browser->script('document.getElementsByName("taxonomy[tag][]")[0].selectedIndex = 0');
            $browser->script('document.getElementsByName("meta[meta_keyword]")[0].value = "'.$faker->name.'"');
            $browser->script('document.querySelectorAll("[type=submit]")[0].click()');

            $browser->waitForText('Master Data of Post')
                    ->assertSee('Successfully Add Post!');
        });
    }

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testEditPost()
    {
        $user = \App\User::find(1);
        $faker = \Faker\Factory::create();

        $this->browse(function (Browser $browser) use ($user, $faker) {

            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\PostController@index'))
                    ->assertSee('Master Data of Post')
                    ->waitForText('Actions')
                    ->clickLink('Actions')
                    ->clickLink('Edit')
                    ->AssertSee('Post Form')
                    ->type('post[post_title]', $faker->name)
                    ->type('post[post_slug]', $faker->name)
                    ->type('meta[meta_title]', $faker->name)
                    ->type('meta[meta_description]', $faker->text);

            $browser->script('document.getElementsByName("post[post_content]")[0].value = "'.$faker->text.'"');
            $browser->script('document.getElementsByName("post[post_status]")[0].checked = true');
            $browser->script('document.getElementsByName("post[comment_status]")[0].checked = true');
            //$browser->script('document.getElementsByName("post[post_parent]")[0].selectedIndex = 1'); Disable For A while
            $browser->script('document.getElementsByName("taxonomy[category][]")[0].selectedIndex = 1');
            $browser->script('document.getElementsByName("taxonomy[tag][]")[0].selectedIndex = 0');
            $browser->script('document.getElementsByName("meta[meta_keyword]")[0].value = "'.$faker->name.'"');
            $browser->script('document.querySelectorAll("[type=submit]")[0].click()');

            $browser->waitForText('Master Data of Post')
                    ->assertSee('Successfully Update Post!');
        });
    }

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testDeletePost()
    {
        $user = \App\User::find(1);

        $faker = \Faker\Factory::create();

        $this->browse(function (Browser $browser) use ($user) {

            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\PostController@index'))
                    ->assertSee('Master Data of Post')
                    ->waitForText('Actions')
                    ->clickLink('Actions')
                    ->clickLink('Delete')
                    ->waitForText('Delete Confirmation')
                    ->press('Delete')
                    ->waitForText('Master Data of Post')
                    ->assertSee('Successfully Delete Post!');
        });
    }

}
