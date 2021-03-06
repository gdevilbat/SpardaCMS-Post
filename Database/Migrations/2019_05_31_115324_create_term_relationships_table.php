<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTermRelationshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('term_relationships', function (Blueprint $table) {
            $table->increments('id_term_relationships');
            $table->unsignedInteger('term_taxonomy_id');
            $table->unsignedBigInteger('object_id');
            $table->integer('term_order')->default(0);
            $table->timestamps();
        });

        Schema::table('term_relationships', function($table){
            $table->foreign('term_taxonomy_id')->references(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey())->on('term_taxonomy')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('object_id')->references(\Gdevilbat\SpardaCMS\Modules\Post\Entities\Post::getPrimaryKey())->on('posts')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('term_relationships');
    }
}
