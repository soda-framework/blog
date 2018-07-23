<?php
$blogName = $blog->getSetting('name');
$baseUrl = URL::to($blog->getSetting('slug'));

$currentUrl = URL::current();

$atomId = preg_replace('#^https?://#', '', rtrim($currentUrl,'/')); // Remove http
$atomId = str_replace('www.', '', $atomId); // Remove www.
$atomId = str_replace('#', '/', $atomId); // Replaces all # with /
$atomId = 'tag:' . $atomId;

$lastUpdated = $posts->max('updated_at');
?>

{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{{ strip_tags($blogName) }}</title>
    <subtitle>{{ strip_tags($blogName) }}</subtitle>
    <link href="{{ route('soda.blog.rss') }}" rel="self" />
    <link href="{{ $baseUrl }}" />
    <id>{{ atom_url($currentUrl) }}</id>
    <updated>{{ $lastUpdated->toRFC3339String() }}</updated>

    @foreach($posts as $post)
        <entry>
            <title>{{ strip_tags($post->getTitle()) }}</title>
            <link href="{{ $baseUrl . '/' . trim($post->slug, '/') }}" />
            <id>{{ atom_url($baseUrl . '/' . trim($post->slug, '/')) }}</id>
            <updated>{{ $post->updated_at->toRFC3339String() }}</updated>

            @if($post->featured_image)
            <media:content xmlns:media="http://search.yahoo.com/mrss/" url="{{ preg_match('/^\/\//', $post->featured_image) ? 'http:'.$post->featured_image : $post->featured_image }}" width="450" height="450" medium="image"
                           type="image/jpeg"></media:content>
            @endif

            @if($post->content)
                @if($blog->getSetting('rss_strip_tags'))
                    <content>
                        <![CDATA[
                            {{ strip_tags($post->content) }}</content>
                        ]]>
                    </content>
                @else
                    <content type="xhtml">
                        <div xmlns="http://www.w3.org/1999/xhtml">
                            <![CDATA[
                                {{ $post->content }}
                            ]]>
                        </div>
                    </content>
                @endif
            @endif

            @if($author = $post->getAuthorName())
                <author>
                    <name>{{ $author }}</name>
                </author>
            @endif
        </entry>
    @endforeach
</feed>
