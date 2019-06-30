<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id_posts');
            $table->text('post_title');
            $table->string('post_slug')->unique();
            $table->longText('post_content')->nullable();
            $table->text('post_excerpt')->nullable();
            $table->string('post_status', 20)->default('publish');
            $table->string('comment_status', 20)->default('open');
            $table->string('ping_status', 20)->default('open');
            $table->string('post_password')->nullable();
            $table->text('to_ping')->nullable();
            $table->text('pinged')->nullable();
            $table->text('post_content_filtered')->nullable();
            $table->unsignedBigInteger('post_parent')->nullable();
            $table->text('guid')->nullable();
            $table->integer('menu_order')->default(0);
            $table->string('post_type', 20)->default('post');
            $table->string('post_mime_type', 20)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modified_by');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('posts', function($table){
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('post_parent')->references(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey())->on('posts')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
