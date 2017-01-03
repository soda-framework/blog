<?php

namespace Soda\Blog\Http\Controllers;

use Illuminate\Http\Request;
use Soda\Blog\Models\Post;

class PostController
{
    protected $currentBlog;

    public function __construct()
    {
        $this->currentBlog = app('CurrentBlog');
    }

    public function index(Request $request)
    {
        $sort = $request->input('sort');
        $search = $request->input('search');
        $direction = strtolower($request->input('direction'));

        if ($direction != 'asc' && $direction != 'desc') {
            $direction = 'desc';
        }

        $posts = $this->currentBlog->posts()->whereNotNull('id');

        if ($sort) {
            $posts = $posts->withoutGlobalScope('position')->orderBy($sort, $direction);
        }

        if ($search) {
            $posts = $posts->whereRaw('MATCH(name,singletags,content) AGAINST (?)', [$search]);
        }

        return view('soda-blog::post-list', [
            'blog'  => $this->currentBlog,
            'posts' => $posts->paginate(),
        ]);
    }

    public function create()
    {
        return view('soda-blog::post-edit', [
            'blog'     => $this->currentBlog,
            'post'     => new Post,
            'settings' => $this->currentBlog->postDefaultSettings->groupBy('name'),
        ]);
    }

    public function edit($id)
    {
        $post = $this->currentBlog->posts()->with('blog.postDefaultSettings')->findOrFail($id);
        $defaultSettings = $post->blog->postDefaultSettings;
        $allSettings = collect();

        // Iterate over our default settings
        // We will fill an array of 'allSettings', containing values
        // for any settings we do have
        foreach ($defaultSettings as $key => $defaultSetting) {
            $existingSettings = $post->settings->filter(function ($setting) use ($defaultSetting) {
                return $setting->name == $defaultSetting->name;
            });

            //$fl would be items that should replace.
            if ($existingSettings) {
                foreach ($existingSettings as $setting) {
                    $allSettings->push($setting);
                }
            } else {
                $allSettings->push($defaultSetting);
            }
        }

        return view('soda-blog::posts.view', [
            'blog'     => $this->currentBlog,
            'post'     => $post,
            'settings' => $allSettings,
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $post = $id ? $this->currentBlog->posts()->findOrFail($id) : new Post;

        dd($post, $request);
        /*

        $input['published_at'] = $input['published_at'] ? Carbon::createFromFormat('Y-m-d H:i:s', $input['published_at'], 'Australia/Sydney')->setTimezone('UTC') : Carbon::now('Australia/Sydney')->setTimezone('UTC');
        $input['blog_id'] = $blog->id;
        if (isset($input['preview']) && $input['preview'] == true) {
            $input['status'] = Content::DRAFT_STATUS;
        } else {
            if (isset($input['status'])) {
                $input['status'] = $input['status'];
            } else {
                $input['status'] = Content::LIVE_STATUS;
            }
        }
        $input['view'] = $blog->single_view;
        $input['blog_id'] = $blog->id;

        //SLUGS:
        if (!@$input['slug'] || @$input['slug'] == '') {
            $input['slug'] = Utils::slugify($input['name']);
        } else {
            $input['slug'] = Utils::slugify($input['slug']);
        }
        //check for duplicate slugs..

        if (@$id) {
            $duplicateSlugs = $blog->posts()->where('slug', $input['slug'])->where('id', '!=', $id)->first();
        } else {
            $duplicateSlugs = $blog->posts()->where('slug', $input['slug'])->first();
        }
        if ($duplicateSlugs) {
            $input['slug'] = $input['slug'].'-'.uniqid();
        }
        $input['slug'] = '/'.trim($input['slug'], '/');

        //PUBLISHED
        if (!strtotime($input['published_at'])) {
            $input['published_at'] = date('Y-m-d H:i:s');
        }

        //EXCERPT
        if (!$input['excerpt'] && @$input['content']) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($input['content']);
            libxml_clear_errors();
            $ps = $doc->getElementsByTagName('p');
            $p = $ps->item(0)->nodeValue;
            //first 30 words of this..
            $input['export'] = implode(' ', array_slice(explode(' ', $p), 0, 30));
        }

        //FEATURED IMAGE
        if (@$input['setting']['Featured Image']) {
            $input['featured_image'] = $input['setting']['Featured Image']['stdClass'][0];
        }

        //USER
        $input['user_id'] = @\Auth::user()->id;

        if (@$post->id) {
            $post->update($input);
        } else {
            $post = $post->create($input);
        }

        if ($input['singletags']) {
            //we have tags - we need to save 'em
            $tagsArray = explode(',', $input['singletags']);
            $tagModel = [];

            //detach all existing tag items and re-attach them.
            $post->tags()->detach();

            foreach ($tagsArray as $tag) {
                $tagModel[] = Tag::firstOrNew(['name' => $tag]);
            }
            foreach ($tagModel as $tm) {
                $tm->save();
                if (!$post->tags->contains($tm->id)) {
                    $post->tags()->save($tm);
                }
            }
        }

        if (isset($input['setting'])) {
            foreach ($input['setting'] as $name => $settingGroup) {
                if ($name != 'Featured Image') { //we don't want the featured image as a setting, but it comes through on a settings becuase of the upload stuff.
                    foreach ($settingGroup as $type => $setGrp) {
                        foreach ($setGrp as $key => $setting) {
                            //we want to delete this setting.
                            $toDel = Utils::recursive_array_search('deleted', $setGrp);
                            if (is_array($setGrp) && @$toDel) {
                                $postSetting = PostSetting::destroy($toDel);
                            } else {

                                $postSetting = PostSetting::withTrashed()
                                    ->where('name', '=', $name)
                                    ->where('post_id', '=', $post->id)
                                    ->where('id', '=', $key)->first();

                                //if it's not found (even in trashed) then we need to make a new field.
                                if (!$postSetting) {
                                    $postSetting = new PostSetting();
                                }

                                //grab the original post settings default that this is coming from:
                                $postSettingDefault = BlogPostSettingsDefault::where('name', '=', $name)
                                    ->where('blog_id', $blog->id)->first();

                                //otherwise this field exists.. we can overwrite it' settings.
                                $postSetting->name = $name;
                                $postSetting->value = $setting; // proposed fix $postSetting->value = is_array($setting) ? implode(',',$setting) : $setting;
                                $postSetting->post_id = $post->id;
                                $postSetting->field_type = @$postSetting->field_type ? $postSetting->field_type : $postSettingDefault->field_type;

                                //dd($skuSetting);
                                $postSetting->save();
                                $postSetting->restore();     //TODO: do we always want to restore the deleted field here?
                            }
                        }
                    }
                }
            }
        }
        if (@$input['preview'] == true) {
            Session::put('blog_preview', true);

            return Redirect::to($blog->slug.$post->slug);
        } else {
            return Redirect::action('\Bootleg\Blog\PostController@getView', [$post->id])->with('success', 'Post Updated.');
        }
        */
    }

    public function delete($id)
    {
        $post = $this->currentBlog->posts()->with('tags')->findOrFail($id);

        $post->tags()->detach($post->tags->pluck('id'));
        $post->delete();

        return redirect()->route('soda.cms.blog.index')->with('success', 'Blog Post Deleted');
    }
}
