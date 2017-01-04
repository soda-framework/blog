<?php
// Get current URL
$currentUrl = URL::current();
$baseUrl = URL::to($blog->slug);

$atomId = preg_replace('#^https?://#', '', rtrim($currentUrl,'/')); // Remove http
$atomId = str_replace('www.', '', $atomId); // Remove www.
$atomId = str_replace('#', '/', $atomId); // Replaces all # with /
$atomId = 'tag:' . $atomId;

$lastUpdated = $posts->max('updated_at');
?>

{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{{ strip_tags($blog->name) }}</title>
    <subtitle>{{ strip_tags($blog->name) }}</subtitle>
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

            @if($post->excerpt)
                @if(config('soda.blog.rss.strip_tags'))
                    <content>{{ strip_tags($post->excerpt) }}</content>
                @else
                    <content type="xhtml">
                        <div xmlns="http://www.w3.org/1999/xhtml">
                            {{ $post->excerpt }}
                        </div>
                    </content>
                @endif
            @endif

            @if($post->content)
                @if(config('soda.blog.rss.strip_tags'))
                    <content>{{ strip_tags($post->content) }}</content>
                @else
                    <content type="xhtml">
                        <div xmlns="http://www.w3.org/1999/xhtml">
                            {{ $post->content }}
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
