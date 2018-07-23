<?php

namespace Soda\Blog\InstantArticles;

use Soda\Blog\Models\Post;

interface InstantArticleParser
{
    public function render(Post $post);
}
