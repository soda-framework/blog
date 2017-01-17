<?php

$blog = app('CurrentBlog');

if ($blog->id) {
    Route::group(['middleware' => ['web', 'soda.web', 'soda.auth']], function () use ($blog) {
        $blog_cms_slug = config('soda-blog.cms_slug', 'blog');

        Route::group(['prefix' => config('soda.cms.path').'/'.trim($blog_cms_slug, '/')], function () {
            Route::get('/', 'BlogController@index')->name('soda.cms.blog.index');
            Route::get('create', 'BlogController@create')->name('soda.cms.blog.create');
            Route::get('edit/{id}', 'BlogController@edit')->name('soda.cms.blog.edit');
            Route::post('edit/{id?}', 'BlogController@save')->name('soda.cms.blog.save');
            Route::post('delete/{id}', 'BlogController@delete')->name('soda.cms.blog.delete');

            Route::get('settings', 'BlogController@settings')->name('soda.cms.blog.settings');
            Route::post('settings', 'BlogController@saveSettings')->name('soda.cms.blog.settings-save');

            Route::post('sort', '\Rutorika\Sortable\SortableController@sort')->name('soda.cms.blog.sort');

            Route::group(['prefix' => 'import'], function () {
                Route::get('/', 'ImportController@index')->name('soda.cms.blog.import');
                Route::get('tumblr', 'ImportController@getTumblr')->name('soda.cms.blog.import.tumblr');
                Route::post('tumblr', 'ImportController@postTumblr')->name('soda.cms.blog.import.tumblr.save');
                Route::any('wordpress', 'ImportController@anyWordpress')->name('soda.cms.blog.import.wordpress');
            });
        });
    });

    if (isset($blog) && $blog) {
        Route::group(['prefix' => trim($blog->slug, '/')], function () use ($blog) {
            if ($blog->list_view) {
                Route::get('/', 'FrontendController@showListing')->name('soda.blog.listing');
            }

            if ($blog->rss_enabled == true) {
                Route::get($blog->rss_slug, 'FrontendController@rss')->name('soda.blog.rss');
            }
        });
    }
}
