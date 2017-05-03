<?php

namespace Soda\Blog;

use Illuminate\Contracts\Routing\Registrar as RouterContract;
use Illuminate\Http\Request;
use Soda\Cms\Http\RequestMatcher\Matchers\MatcherInterface;

class SluggedBlogPostMatcher implements MatcherInterface
{
    protected $currentBlog;
    protected $router;
    protected $match;

    public function __construct(RouterContract $router)
    {
        $this->currentBlog = app('CurrentBlog');
        $this->router = $router;
    }

    public function matches($slug)
    {
        $blogSlug = trim($this->currentBlog->getSetting('slug'), '/');
        $postSlug = trim($slug, '/');
        $postSlug = '/' . trim(substr($postSlug, strlen($blogSlug), strlen($postSlug)), '/');

        $this->match = $this->currentBlog->posts()->with('tags', 'author', 'settings')->where('slug', $postSlug)->first();

        return $this->match;
    }

    public function render(Request $request)
    {
        return function () {
            return view($this->match->view ?: $this->currentBlog->single_view, [
                'blog' => $this->currentBlog,
                'post' => $this->match,
            ]);
        };
    }
}
