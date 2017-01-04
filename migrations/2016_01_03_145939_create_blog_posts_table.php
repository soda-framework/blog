<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBlogPostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('blog_posts', function(Blueprint $table)
		{
			$table->engine = 'MyISAM';

			$table->increments('id');
			$table->string('name');
			$table->text('content', 16777215);
			$table->text('excerpt', 65535);
			$table->string('slug');
			$table->string('singletags');
			$table->string('featured_image');
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
