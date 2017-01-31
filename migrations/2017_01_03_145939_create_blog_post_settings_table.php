<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogPostSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_post_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('value', 65535);
            $table->integer('field_id')->unsigned()->index('FK_blog_post_settings_fields');
            $table->integer('post_id')->unsigned()->index('FK_blog_post_settings_blog_posts');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('blog_post_settings');
    }
}
