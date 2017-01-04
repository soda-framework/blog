<?php

namespace Soda\Blog\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Soda\Blog\Models\Post;
use Soda\Blog\Models\Tag;

class BlogController
{
    protected $currentBlog;

    public function __construct()
    {
        $this->currentBlog = app('CurrentBlog');
    }

    public function settings()
    {
        return view('soda-blog::blog-settings', [
            'blog' => $this->currentBlog,
        ]);
    }

    public function saveSettings(Request $request)
    {
        $this->currentBlog->fill($request->only([
            'name',
            'slug',
            'single_view',
            'list_view',
            'rss_enabled',
            'rss_slug',
            'rss_view',
            'rss_strip_tags',
        ]))->save();

        return redirect()->route('soda.cms.blog.settings')->with('success', 'Blog updated successfully');
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

        return view('soda-blog::post-edit', [
            'blog'     => $this->currentBlog,
            'post'     => $post,
            'settings' => $allSettings,
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $post = $id ? $this->currentBlog->posts()->findOrFail($id) : new Post;

        $post->fill($request->only([
            'name',
            'status',
            'excerpt',
            'content',
        ]));

        // Only make changes if this post is newly created
        if(!$post->id) {
            // Set the post author
            $post->user_id = Auth::user()->id;

            // Make sure the post is assigned to the right blog
            $post->blog_id = $this->currentBlog->id;
        }

        // Generate a valid slug
        if($request->has('slug')) {
            $post->slug = '/';
            $post->slug = $post->generateSlug($request->input('slug'), false);
        }

        // Format the publish date to the correct timezone
        if($request->input('published_at')) {
            $post->published_at = \SodaForm::datetime([
                'field_name'   => 'published_at',
                'field_params' => [
                    'timezone' => config('soda.blog.publish_timezone'),
                ]
            ])->getSaveValue($request);
        }

        // Handle tags
        if ($request->has('singletags')) {
            $post->singletags = implode(',', $request->input('singletags'));
            $tags = collect();

            foreach ($request->input('singletags') as $tag) {
                $tags->push(Tag::firstOrCreate(['name' => $tag]));
            }

            $post->tags()->sync($tags->pluck('id')->toArray());
        }

        // Save post to the database
        $post->save();
        /*


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

        return redirect()->route('soda.cms.blog.edit', $post->id)->with('success', ucfirst(trans('soda-blog::general.post')) . ' updated successfully');
    }

    public function delete($id)
    {
        $post = $this->currentBlog->posts()->with('tags')->findOrFail($id);

        $post->tags()->detach();
        $post->delete();

        return redirect()->route('soda.cms.blog.index')->with('success', 'Blog Post Deleted');
    }
}
