<?php

namespace Soda\Blog\Http\Controllers;

use DOMDocument;
use Soda\Blog\Models\Tag;
use Soda\Blog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController
{
    protected $currentBlog;

    public function __construct()
    {
        set_time_limit(-1);
        ini_set('max_execution_time', 0);
        ini_set('default_socket_timeout', 900);

        $this->currentBlog = app('CurrentBlog');
    }

    public function index()
    {
        return view('soda-blog::import.index');
    }

    //imports stuff from tumblr..
    public function getTumblr()
    {
        return view('soda-blog::import.tumblr');
    }

    //import tumblr..
    public function postTumblr($offset = 0, $start = true)
    {
        $total = $offset;
        do {
            $response = $this->importTumblr($total, $start);
            $start = false;
            if ($response) {
                $total += $response;
            }
        } while ($response);
    }

    public function anyWordpress()
    {
        echo '<h1>TODO</h1>';
    }

    public function importTumblr(Request $request, $offset = 0, $start = true)
    {
        if ($request->input('offset') && $start) {
            $offset = $request->input('offset');
        }

        $tumblrUrl = $request->input('tumblr_url');
        $tumblrApiKey = $request->input('tumblr_api_key');

        if ($tumblrUrl && $tumblrApiKey) {
            $url = "http://api.tumblr.com/v2/blog/$tumblrUrl/posts?api_key=$tumblrApiKey&&offset=$offset";
            $tumblr = json_decode(file_get_contents($url));
            $count = $tumblr->response->blog->posts;
            $importedPosts = 0;
            foreach ($tumblr->response->posts as $tumblrPost) {
                $importedPosts++;
                //existing:
                $existing = Post::where('name', $tumblrPost->title)->first();
                if ($existing && ($tumblrPost->slug != '' || $tumblrPost->slug != '/')) {
                    echo 'Post already exists<br />';
                    flush();
                } else {
                    if (isset($tumblrPost->body) && $tumblrPost->body) {
                        $post = new Post([
                            'name'         => $tumblrPost->title,
                            'published_at' => date('Y-m-d H:i:s', strtotime($tumblrPost->date)),
                            'slug'         => '/'.$tumblrPost->slug,
                            'status_id'    => 1,
                            'blog_id'      => $this->currentBlog->id,
                            'view'         => $this->currentBlog->single_view,
                        ]);

                        // Generate slug if imported slug is invalid
                        if ($post->slug == '' || $post->slug == '/') {
                            $post->slug = '/'.uniqid();
                            echo 'Can not get slug for "'.$post->name.'" - using random string instead<br />';
                            flush();
                        }

                        echo 'Saving post: '.$post->slug.'<br />';
                        flush();

                        if ($request->input('rehost')) {
                            //we need to take the content, rip out all the images and re-host them with us..
                            $rehost = $this->rehost($tumblrPost->body);
                            $post->content = $rehost['content'];
                            $post->featured_image = $rehost['image'];
                        } else {
                            $post->content = $tumblrPost->body;
                            $images = $this->getElementsByTag($tumblrPost->body, 'img');
                            $post->featured_image = @$images->item(0)->getAttribute('src');
                        }

                        // Get the first paragraph
                        $paragraphs = $this->getElementsByTag(mb_convert_encoding($post->content, 'HTML-ENTITIES', 'UTF-8'), 'p');
                        $firstParagraph = @$paragraphs->item(0)->nodeValue;

                        // Strip down to first 30 words
                        $post->excerpt = $this->fixEncoding(implode(' ', array_slice(explode(' ', $firstParagraph), 0, 30)));

                        echo 'Post saved: '.$post->slug.'<br />';
                        flush();

                        // Save tags
                        foreach ($tumblrPost->tags as $tumblrTag) {
                            $existing = Tag::where('name', $tumblrTag)->first();
                            if ($existing) {
                                $existing->posts()->attach($post->id);
                            } else {
                                $tag = Tag::create(['name' => $tumblrTag]);
                                $tag->posts()->attach($post->id);
                            }

                            if ($post->singletags) {
                                $post->singletags .= ", $tumblrTag";
                            } else {
                                $post->singletags = $tumblrTag;
                            }
                        }

                        $post->save();
                        flush();
                    }
                }
            }
            if ($count > $offset) {
                //we need to recurse to another page of tumblrs.
                return $importedPosts;
            }
        } else {
            return;
        }
    }

    protected function getElementsByTag($body, $tag)
    {
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML($body);
        libxml_clear_errors();
        $elements = $doc->getElementsByTagName($tag);

        return $elements;
    }

    //in goes html, out comes html, with hosted images on s3.
    protected function rehost($html)
    {
        $driver = config('soda.upload.driver');
        $url_prefix = trim(config('soda.upload.folder'), '/');

        $images = $this->getElementsByTag($html, 'img');

        $first = null;
        foreach ($images as $img) {
            $originalUrl = $img->getAttribute('src');
            echo 'Rehosting image: '.$originalUrl.'<br />';
            flush();
            $file = @file_get_contents($originalUrl);
            if ($file) {
                $unique = uniqid();
                $path_info = pathinfo($originalUrl);
                $final_path = ltrim($url_prefix.'/', '/').$path_info['filename'].'__'.$unique;
                if ($path_info['extension']) {
                    $final_path .= '.'.$path_info['extension'];
                }

                Storage::disk($driver)->put(
                    $final_path,
                    $file, 'public'
                );

                $rehostedUrl = $driver == 'soda.public' ? '/uploads/'.$final_path : Storage::disk($driver)->url(trim($final_path, '/'));

                echo 'Image saved to: '.$rehostedUrl.'<br />';

                if (! $first) {
                    echo 'Setting featured to: '.$rehostedUrl;
                    $first = $rehostedUrl;
                }
                flush();
                //and we can now replace it in our original html..
                $html = str_replace($originalUrl, $rehostedUrl, $html);
            } else {
                echo 'File 404 </br>';
            }
        }

        return ['content' => $html, 'image' => $first];
    }

    public function fixEncoding($str)
    {
        $chr_map = [
            // Windows codepage 1252
            "\xC2\x82"     => "'", // U+0082⇒U+201A single low-9 quotation mark
            "\xC2\x84"     => '"', // U+0084⇒U+201E double low-9 quotation mark
            "\xC2\x8B"     => "'", // U+008B⇒U+2039 single left-pointing angle quotation mark
            "\xC2\x91"     => "'", // U+0091⇒U+2018 left single quotation mark
            "\xC2\x92"     => "'", // U+0092⇒U+2019 right single quotation mark
            "\xC2\x93"     => '"', // U+0093⇒U+201C left double quotation mark
            "\xC2\x94"     => '"', // U+0094⇒U+201D right double quotation mark
            "\xC2\x9B"     => "'", // U+009B⇒U+203A single right-pointing angle quotation mark

            // Regular Unicode     // U+0022 quotation mark (")
            // U+0027 apostrophe     (')
            "\xC2\xAB"     => '"', // U+00AB left-pointing double angle quotation mark
            "\xC2\xBB"     => '"', // U+00BB right-pointing double angle quotation mark
            "\xE2\x80\x98" => "'", // U+2018 left single quotation mark
            "\xE2\x80\x99" => "'", // U+2019 right single quotation mark
            "\xE2\x80\x9A" => "'", // U+201A single low-9 quotation mark
            "\xE2\x80\x9B" => "'", // U+201B single high-reversed-9 quotation mark
            "\xE2\x80\x9C" => '"', // U+201C left double quotation mark
            "\xE2\x80\x9D" => '"', // U+201D right double quotation mark
            "\xE2\x80\x9E" => '"', // U+201E double low-9 quotation mark
            "\xE2\x80\x9F" => '"', // U+201F double high-reversed-9 quotation mark
            "\xE2\x80\xB9" => "'", // U+2039 single left-pointing angle quotation mark
            "\xE2\x80\xBA" => "'", // U+203A single right-pointing angle quotation mark
        ];
        $chr = array_keys($chr_map); // but: for efficiency you should
        $rpl = array_values($chr_map); // pre-calculate these two arrays
        return str_replace($chr, $rpl, html_entity_decode($str, ENT_QUOTES, 'UTF-8'));
    }
}
