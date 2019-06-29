<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TagTest extends DuskTestCase
{
    use DatabaseMigrations, \Gdevilbat\SpardaCMS\Modules\Core\Tests\ManualRegisterProvider;
    
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testCreateTag()
    {
        $user = \App\User::find(1);
        $faker = \Faker\Factory::create();

        $this->browse(function (Browser $browser) use ($user, $faker) {
            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\TagController@index'))
                    ->assertSee('Master Data of Tags')
                    ->clickLink('Add New Tag')
                    ->waitForText('Tag Form')
                    ->AssertSee('Tag Form')
                    ->type('term[name]', $faker->word)
                    ->type('term[slug]', $faker->word)
                    ->type('taxonomy[description]', $faker->text)
                    ->press('Submit')
                    ->waitForText('Master Data of Tags')
                    ->assertSee('Successfully Add Tag!');
        });
    }

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testEditTag()
    {
        $user = \App\User::find(1);

        $this->browse(function (Browser $browser) use ($user) {

            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\TagController@index'))
                    ->assertSee('Master Data of Tags')
                    ->waitForText('Actions')
                    ->clickLink('Actions')
                    ->clickLink('Edit')
                    ->AssertSee('Tag Form')
                    ->press('Submit')
                    ->waitForText('Master Data of Tags')
                    ->assertSee('Successfully Update Tag!');
        });
    }

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testDeleteTag()
    {
        $user = \App\User::find(1);

        $faker = \Faker\Factory::create();

        $this->browse(function (Browser $browser) use ($user) {

            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\TagController@index'))
                    ->assertSee('Master Data of Tags')
                    ->waitForText('Actions')
                    ->clickLink('Actions')
                    ->clickLink('Delete')
                    ->waitForText('Delete Confirmation')
                    ->press('Delete')
                    ->waitForText('Master Data of Tags')
                    ->assertSee('Successfully Delete Taxonomy!');
        });
    }
}
