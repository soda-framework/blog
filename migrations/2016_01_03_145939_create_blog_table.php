<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBlogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('blog', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('slug');
			$table->integer('application_id')->nullable()->unsigned()->index('FK_blog_applications');
			$table->string('single_view')->default('soda-blog::default.single');
            $table->string('list_view')->default('soda-blog::default.list');
            $table->integer('rss_enabled', 1)->unsigned()->default(1);
            $table->string('rss_slug')->default('rss');
            $table->string('rss_view')->default('soda-blog::default.rss');
            $table->integer('rss_strip_tags', 1)->unsigned()->default(1);
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
		Schema::drop('blog');
	}

}
