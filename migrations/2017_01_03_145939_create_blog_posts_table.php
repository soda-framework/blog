<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::defaultStringLength(191);
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->string('name');
            $table->text('content', 16777215);
            $table->string('slug');
            $table->string('singletags')->nullable();
            $table->string('featured_image')->nullable();
            $table->integer('blog_id')->unsigned()->nullable();
            $table->integer('status')->unsigned();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('position')->unsigned();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE blog_posts ADD FULLTEXT full(name, content, singletags)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('blog_posts');
    }
}
