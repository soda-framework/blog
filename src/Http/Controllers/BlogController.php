<?php

namespace Soda\Blog\Http\Controllers;

class BlogController
{
    protected $currentBlog;

    public function __construct()
    {
        $this->currentBlog = app('CurrentBlog');
    }

    public function settings()
    {
        return view('soda-blog::blog-settings', [
            'blog' => $this->currentBlog,
        ]);
    }
}
