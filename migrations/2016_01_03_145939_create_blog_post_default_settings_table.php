<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBlogPostDefaultSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('blog_post_default_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->text('value', 65535);
			$table->text('field_id', 65535);
            $table->integer('blog_id');
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
		Schema::drop('blog_post_default_settings');
	}

}
