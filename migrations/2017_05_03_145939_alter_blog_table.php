<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBlogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blog', function (Blueprint $table) {
            $table->dropColumn(['name', 'slug', 'rss_enabled', 'rss_slug', 'rss_strip_tags']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blog', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('slug')->after('slug');
            $table->tinyInteger('rss_enabled')->unsigned()->default(1)->after('list_view');
            $table->string('rss_slug')->default('rss')->after('rss_enabled');
            $table->tinyInteger('rss_strip_tags')->unsigned()->default(1)->after('rss_view');
        });
    }
}
