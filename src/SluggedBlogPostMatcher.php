<?php

namespace Soda\Blog;

use Illuminate\Http\Request;
use Soda\Blog\InstantArticles\InstantArticleParser;
use Soda\Cms\Http\RequestMatcher\Matchers\MatcherInterface;
use Illuminate\Contracts\Routing\Registrar as RouterContract;

class SluggedBlogPostMatcher implements MatcherInterface
{
    protected $currentBlog;
    protected $router;
    protected $match;
    protected $isInstantArticle = false;

    const INSTANT_ARTICLE_SLUG = '/fbia.html';

    public function __construct(RouterContract $router)
    {
        $this->currentBlog = app('CurrentBlog');
        $this->router = $router;
    }

    public function matches($slug)
    {
        $blogSlug = trim($this->currentBlog->getSetting('slug'), '/');
        $postSlug = trim($slug, '/');
        $postSlug = '/'.trim(substr($postSlug, strlen($blogSlug), strlen($postSlug)), '/');

        if (ends_with($postSlug, static::INSTANT_ARTICLE_SLUG)) {
            $this->isInstantArticle = true;
            $postSlug = substr($postSlug, 0, strlen($postSlug) - strlen(static::INSTANT_ARTICLE_SLUG));
        }

        $this->match = $this->currentBlog->posts()->with('tags', 'author', 'settings')->where('slug', $postSlug)->first();

        if (! $this->match) {
            $this->isInstantArticle = false;
        }

        return $this->match;
    }

    public function render(Request $request)
    {
        return function () {
            if ($this->isInstantArticle == true) {
                return app(InstantArticleParser::class)->render($this->match);
            }

            return view($this->match->view ?: $this->currentBlog->single_view, [
                'blog' => $this->currentBlog,
                'post' => $this->match,
            ]);
        };
    }
}
