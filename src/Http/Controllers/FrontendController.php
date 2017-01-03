<?php

namespace Soda\Blog\Http\Controllers;

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
            'posts' => $this->currentBlog->posts()->with('tags', 'author')->live()->paginate(5),
        ]);
    }

    public function showPost($slug)
    {
        $slug = '/'.ltrim($slug, '/');
        $post = $this->currentBlog->posts()->with('tags', 'author')->live()->where('slug', $slug)->first();

        if ($post) {
            return view($post->view ?: $this->currentBlog->single_view, [
                'blog' => $this->currentBlog,
                'post' => $post,
            ]);
        }

        abort(404);
    }
}
