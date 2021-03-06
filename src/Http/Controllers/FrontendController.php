<?php

namespace Soda\Blog\Http\Controllers;

use Illuminate\Support\Facades\Config;

class FrontendController
{
    protected $currentBlog;

    public function __construct()
    {
        $this->currentBlog = app('CurrentBlog');
    }

    public function showListing()
    {
        return view($this->currentBlog->list_view, [
            'blog'  => $this->currentBlog,
            'posts' => $this->currentBlog->posts(),
        ]);
    }

    public function showPost($slug)
    {
        $slug = '/'.ltrim($slug, '/');
        $post = $this->currentBlog->posts()->with('tags', 'author', 'settings')->where('slug', $slug)->first();

        if ($post) {
            return view($post->view ?: $this->currentBlog->single_view, [
                'blog' => $this->currentBlog,
                'post' => $post,
            ]);
        }

        abort(404);
    }

    public function rss()
    {
        // Disable debugbar as it breaks the XML format
        Config::set('debugbar.enabled', false);

        $posts = $this->currentBlog->posts()->with('tags', 'author')->paginate(20);

        return response()->view($this->currentBlog->rss_view, [
            'blog'  => $this->currentBlog,
            'posts' => $posts,
        ])->header('Content-Type', 'text/xml');
    }

    public function instantArticles()
    {
        // Disable debugbar as it breaks the XML format
        Config::set('debugbar.enabled', false);

        $posts = $this->currentBlog->posts()->with('tags', 'author')->paginate(20);

        return response()->view($this->currentBlog->instant_article_feed_view, [
            'blog'  => $this->currentBlog,
            'posts' => $posts,
        ])->header('Content-Type', 'text/xml');
    }
}
