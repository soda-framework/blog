<?php

if ( ! function_exists('blog_slug'))
{
    function blog_slug($slug)
    {
        $blog = app('CurrentBlog');
        if ($blog->slug == '/')
        {
            $blog->slug = '';
        }

        $slug = '/' . $blog->slug . '/' . $slug;

        return preg_replace('#/+#', '/', $slug);
    }
}
