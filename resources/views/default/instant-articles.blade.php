<?php
$blogName = $blog->getSetting('name');
$baseUrl = URL::to($blog->getSetting('slug'));
$lastUpdated = $posts->max('updated_at');
?>

<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>{{ strip_tags($blogName) }}</title>
        <link>{{ $baseUrl }}</link>
        <description>
            {{ strip_tags($blogName) }} feed for Facebook Instant Articles
        </description>
        <language>en-us</language>
        <lastBuildDate>{{ $lastUpdated->toRFC3339String() }}</lastBuildDate>
        @foreach($posts as $post)
        <item>
            <title>{{ strip_tags($post->getTitle()) }}</title>
            <link>{{ $baseUrl . '/' . trim($post->slug, '/') }}</link>
            <guid>{{ 'B' . $blog->id . 'P' . $post->id }}</guid>
            <pubDate>{{ $post->published_at->toIso8601String() }}</pubDate>
            @if($author = $post->getAuthorName())
                <author>{{ $author }}</author>
            @endif
            <description>{!! trim(html_entity_decode($posts->first()->getExcerpt(250))) !!}</description>
            <content:encoded>
                <![CDATA[
                {!! app(Soda\Blog\InstantArticles\InstantArticleParser::class)->render($post) !!}
                ]]>
            </content:encoded>
        </item>
        @endforeach
    </channel>
</rss>
