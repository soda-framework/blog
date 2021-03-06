<?php

return [
    'cms_slug' => 'blogs',

    'publish_timezone' => 'Australia/Sydney',

    'default_sort' => [
        'published_at' => 'DESC',
    ],

    'rss' => [
        'enabled'    => true,
        'slug'       => 'rss',
        'view'       => 'soda-blog::default.rss',
        'strip_tags' => true,
    ],

    'field_params' => [
        'name'           => [],
        'featured_image' => [],
        'excerpt'        => [],
        'content'        => [],
        'singletags'     => [],
    ],
];
