<?php

namespace Soda\Blog\InstantArticles;

use Soda\Blog\Models\Post;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Transformer\Transformer;

abstract class AbstractInstantArticleParser implements InstantArticleParser
{
    public function render(Post $post)
    {
        \Logger::getLogger('facebook-instantarticles-transformer')->getParent()->setLevel(\LoggerLevel::getLevelOff());

        $article = InstantArticle::create()->withCanonicalUrl($post->getFullUrl());

        $article->withHeader($this->header($post));

        $this->applyContent($article, $post);

        $article->withFooter($this->footer($post));

        return $article->render('<!doctype html>');
    }

    protected function parseHtml($html)
    {
        libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->loadHTML($html);
        libxml_use_internal_errors(false);

        return $document;
    }

    protected function applyRules(Transformer $transformer)
    {
        $transformer->loadRules(file_get_contents(base_path('vendor/facebook/facebook-instant-articles-sdk-php/src/Facebook/InstantArticles/Parser/instant-articles-rules.json'), true));

        foreach ($this->transformRules() as $configurationRule) {
            $class = $configurationRule['class'];
            try {
                $factoryMethod = new \ReflectionMethod($class, 'createFrom');
            } catch (\ReflectionException $e) {
                $factoryMethod =
                    new \ReflectionMethod(
                        'Facebook\\InstantArticles\\Transformer\\Rules\\'.$class,
                        'createFrom'
                    );
            }
            $transformer->addRule($factoryMethod->invoke(null, $configurationRule));
        }
    }

    abstract protected function header(Post $post);

    abstract protected function applyContent(InstantArticle $article, Post $post);

    abstract protected function footer(Post $post);

    abstract protected function transformRules();
}
