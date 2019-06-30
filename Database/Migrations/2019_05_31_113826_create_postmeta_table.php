<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostmetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postmeta', function (Blueprint $table) {
            $table->bigIncrements('id_postmeta');
            $table->unsignedBigInteger('post_id');
            $table->string('meta_key');
            $table->longText('meta_value')->nullable();
            $table->timestamps();
        });

        Schema::table('postmeta', function($table){
            $table->foreign('post_id')->references(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey())->on('posts')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('postmeta');
    }
}
