<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToBlogPostSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blog_post_settings', function (Blueprint $table) {
            $table->foreign('field_id', 'FK_blog_post_settings_fields')->references('id')->on('fields')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blog_post_settings', function (Blueprint $table) {
            $table->dropForeign('FK_blog_post_settings_fields');
        });
    }
}
