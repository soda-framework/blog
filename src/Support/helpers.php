<?php

if (! function_exists('blog_slug')) {
    function blog_slug($slug)
    {
        $blog = app('CurrentBlog');

        $blogSlug = $blog->getSetting('slug');
        $blogSlug = $blogSlug == '/' ? '' : $blogSlug;

        $slug = '/'.trim($blogSlug, '/').'/'.$slug;

        return preg_replace('#/+#', '/', $slug);
    }
}

if (! function_exists('atom_url')) {
    function atom_url($url)
    {
        $atomId = preg_replace('#^https?://#', '', rtrim($url, '/')); // Remove http
        $atomId = str_replace('www.', '', $atomId); // Remove www.
        $atomId = str_replace('#', '/', $atomId); // Replaces all # with /

        return 'tag:'.$atomId;
    }
}
