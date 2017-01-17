<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToBlogPostTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blog_post_tag', function (Blueprint $table) {
            $table->foreign('tag_id', 'FK_blog_post_tag_blog_tags')->references('id')->on('blog_tags')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blog_post_tag', function (Blueprint $table) {
            $table->dropForeign('FK_blog_post_tag_blog_tags');
        });
    }
}
