<?php

return [
    'cms_slug' => 'blogs',

    'publish_timezone' => 'Australia/Sydney',

    'default_sort' => [
        'position'     => 'ASC',
        'published_at' => 'DESC',
    ],

    'rss' => [
        'enabled'    => true,
        'slug'       => 'rss',
        'view'       => 'soda-blog::default.rss',
        'strip_tags' => true,
    ],
];
