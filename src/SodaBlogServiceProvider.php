<?php

namespace Soda\Blog;

use Soda\Blog\InstantArticles\DefaultParser;
use Soda\Blog\InstantArticles\InstantArticleParser;
use Soda\Blog\Models\Blog;
use Soda\Blog\Models\Post;
use Soda\Blog\Console\Create;
use Soda\Blog\Console\Install;
use Illuminate\Support\Facades\Route;
use Soda\Cms\Support\Facades\SodaFacade as Soda;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class SodaBlogServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Soda\Blog\Http\Controllers';

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        include_once __DIR__.'/Support/helpers.php';

        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'soda-blog');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'soda-blog');
        $this->publishes([__DIR__.'/../config' => config_path('soda')], 'soda.blog.config');

        $this->app->config->set('sortable.entities.soda-blog-post', Post::class);

        //we don't want to attach events when runnning artisan commands etc..
        if (! $this->app->runningInConsole()) {
            $blog = app('CurrentBlog');
            view()->share('blog', $blog);

            if ($blog->id) {
                app('soda.menu')->menu('sidebar', function ($menu) use ($blog) {
                    $blog_cms_slug = config('soda-blog.cms_slug', 'blog');

                    $menu->addItem('Blog', [
                        'url'         => route('soda.cms.blog.index'),
                        'label'       => ucfirst(trans('soda-blog::general.blog')).' '.ucfirst(trans('soda-blog::general.posts')),
                        'icon'        => 'fa fa-book',
                        'isCurrent'   => soda_request_is(trim($blog_cms_slug, '/').'*') && ! soda_request_is(trim($blog_cms_slug, '/').'/settings*') && ! soda_request_is(trim($blog_cms_slug, '/').'/import*'),
                        'permissions' => 'manage-blog',
                    ]);
                });
            }
        }

        app('soda.drafting')->registerDraftables([Post::class]);
        app('soda.request-matcher')->registerMatcher(SluggedBlogPostMatcher::class);

        parent::boot();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/blog.php', 'soda.blog');

        $this->commands([
            Install::class,
            Create::class,
        ]);

        $this->app->singleton('CurrentBlog', function () {
            $application = app('soda')->getApplication();

            return $application ? Blog::firstOrNew(['application_id' => $application->id]) : new Blog;
        });

        $this->app->singleton(InstantArticleParser::class, DefaultParser::class);
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        Route::group(['namespace' => $this->namespace], function ($router) {
            require __DIR__.'/../routes/web.php';
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
    }
}
