<?php

return [
    'cms_slug' => 'blogs',

    'publish_timezone' => 'Australia/Sydney',

    'default_sort' => [
        'position'     => 'ASC',
        'publish_date' => 'DESC',
    ],

    'rss' => [
        'enabled'    => true,
        'slug'       => 'rss',
        'view'       => 'soda-blog::default.rss',
        'strip_tags' => true,
    ],
];
