<?php

namespace Soda\Blog\InstantArticles;

use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Footer;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Time;
use Facebook\InstantArticles\Transformer\Transformer;
use Soda\Blog\Models\Post;

class DefaultParser extends AbstractInstantArticleParser implements InstantArticleParser
{
    protected function header(Post $post)
    {
        $header = Header::create()
            ->withTitle($post->getTitle())
            ->withPublishTime(
                Time::create(Time::PUBLISHED)->withDatetime($post->publish_date)
            )
            ->withModifyTime(
                Time::create(Time::MODIFIED)->withDatetime($post->modified_date)
            );

        if ($authorName = $post->getAuthorName()) {
            $header->addAuthor(
                Author::create()->withName($authorName)
            );
        }

        return $header;
    }

    protected function applyContent(InstantArticle $article, Post $post)
    {
        $this->transformContent($article, $post->content);

        return $this;
    }

    protected function footer(Post $post)
    {
        return Footer::create()->withCredits('© ' . app('soda')->getApplication()->name);
    }

    protected function transformContent(InstantArticle $article, $html)
    {
        $transformer = new Transformer();
        $this->applyRules($transformer);

        $transformer->transform($article, $this->parseHtml($html));
    }

    protected function transformRules()
    {
        return [];
    }
}
