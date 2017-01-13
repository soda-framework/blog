<?php

namespace Soda\Blog\Http;

use Illuminate\Contracts\Routing\Registrar as RouterContract;
use Illuminate\Http\Request;
use Soda\Cms\Http\RequestMatcher\Matchers\AbstractPageMatcher;
use Soda\Cms\Http\RequestMatcher\Matchers\MatcherInterface;

class BlogPostMatcher extends AbstractPageMatcher implements MatcherInterface
{
    protected $blog;
    protected $router;
    protected $matchedBlogPost;

    public function __construct(RouterContract $router)
    {
        $this->blog = app('CurrentBlog');
        $this->router = $router;
    }

    public function match($slug)
    {
        $blogSlug = trim($this->blog->slug, '/');
        $slug = trim($slug, '/');

        if(starts_with($slug, $blogSlug))
        {
            $slug = substr($slug, strlen($blogSlug));
            $slug = '/' . ltrim($slug, '/');

            $this->matchedBlogPost = $this->blog->posts()->with('settings')->where('slug', $slug)->first();
        }

        return $this->matchedBlogPost;
    }

    public function render(Request $request)
    {
        $fullSlug = str_replace('//', '/', '/' . trim($this->blog->slug, '/') . '/' . trim($this->matchedBlogPost->slug, '/'));

        return $this->dispatchSluggedRoute($request, $fullSlug, function () {
            $view = $this->blog->single_view;

            return view($view, ['post' => $this->matchedBlogPost]);
        });
    }
}
