<?php

namespace Soda\Blog\Http\Controllers;

use Carbon\Carbon;
use Soda\Blog\Models\Tag;
use Soda\Blog\Models\Post;
use Soda\Cms\Models\Field;
use Illuminate\Http\Request;
use Soda\Blog\Models\PostSetting;
use Soda\Cms\Foundation\Uploader;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Validation\ValidatesRequests;

class BlogController extends Controller
{
    use ValidatesRequests;

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
        $status = $request->input('status');

        $direction = strtolower($request->input('direction'));

        if ($direction != 'asc' && $direction != 'desc') {
            $direction = 'desc';
        }

        $posts = $this->currentBlog->posts()->whereNotNull('id');

        if ($sort) {
            $posts = $posts->withoutGlobalScope('position')->orderBy($sort, $direction);
        }

        if ($search) {
            $posts = $posts->searchText($search);
        }

        if ($status == '0' || $status == '1') {
            $posts->where('status', $status);
        }

        if ($status == '2') {
            $posts->where('published_at', '>', Carbon::now());
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
            'settings' => $this->currentBlog->postDefaultSettings,
        ]);
    }

    public function edit($id)
    {
        $post = $this->currentBlog->posts()->with('blog.postDefaultSettings.field', 'settings.field')->findOrFail($id);

        return view('soda-blog::post-edit', [
            'blog'     => $this->currentBlog,
            'post'     => $post,
            'settings' => $this->getPostSettings($post),
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $this->validate($request, [
            'name' => 'required',
            'slug'  => 'required',
        ], [
            'name.required' => 'The title field is required',
        ]);

        $post = $id ? $this->currentBlog->posts()->with('settings')->findOrFail($id) : new Post;

        $post->fill($request->only([
            'name',
            'status',
            'excerpt',
            'content',
        ]));

        // Only make changes if this post is newly created
        if (! $post->id) {
            // Set the post author
            $post->user_id = Auth::user()->id;

            // Make sure the post is assigned to the right blog
            $post->blog_id = $this->currentBlog->id;
        }

        // Generate a valid slug
        if ($request->has('slug')) {
            $post->slug = '/';
            $post->slug = $post->generateSlug($request->input('slug'), false);
        }

        // Format the publish date to the correct timezone
        if ($request->input('published_at')) {
            $post->published_at = \SodaForm::datetime([
                'field_name'   => 'published_at',
                'field_params' => [
                    'timezone' => config('soda.blog.publish_timezone'),
                ],
            ])->getSaveValue($request);
        }

        if(!$post->published_at) {
            $post->published_at = Carbon::now();
        }

        if ($request->hasFile('featured_image')) {
            $post->featured_image = (new Uploader)->uploadFile($request->file('featured_image'));
        }

        // Save post to the database
        $post->save();

        // Handle tags
        if ($request->has('singletags')) {
            $post->singletags = implode(',', $request->input('singletags'));
            $tags = collect();

            foreach ($request->input('singletags') as $tag) {
                $tags->push(Tag::firstOrCreate(['name' => $tag]));
            }

            $post->tags()->sync($tags->pluck('id')->toArray());
        }

        if ($request->has('setting')) {
            $fields = Field::whereIn('id', array_keys($request->input('setting')))->get()->keyBy('id');

            foreach ($request->input('setting') as $fieldId => $settings) {
                $field = $fields->get($fieldId);

                foreach ($settings as $settingName => $settingValue) {
                    $settingModel = PostSetting::firstOrNew([
                        'post_id'  => $post->id,
                        'name'     => $settingName,
                        'field_id' => $fieldId,
                    ])->fill([
                        'value' => \SodaForm::field($field)->setPrefix('setting.'.$field->id)->getSaveValue($request),
                    ]);

                    $post->settings()->save($settingModel);
                }
            }
        }

        return redirect()->route('soda.cms.blog.edit', $post->id)->with('success', ucfirst(trans('soda-blog::general.post')).' updated successfully');
    }

    public function delete($id)
    {
        $post = $this->currentBlog->posts()->with('tags')->findOrFail($id);

        $post->tags()->detach();
        $post->delete();

        return redirect()->route('soda.cms.blog.index')->with('success', ucfirst(trans('soda-blog::general.post')).' deleted');
    }

    protected function getPostSettings(Post $post)
    {
        $defaultSettings = $post->defaultSettings()->with('field')->get();

        $settings = collect();

        // Iterate over our default settings
        // We will fill an array of 'allSettings', containing values
        // for any settings we do have
        foreach ($defaultSettings as $key => $defaultSetting) {
            $existingSettings = $post->settings->filter(function ($setting) use ($defaultSetting) {
                return $setting->name == $defaultSetting->name;
            });

            //$fl would be items that should replace.
            if (count($existingSettings)) {
                foreach ($existingSettings as $setting) {
                    $setting->field->value = $setting->value;
                    $settings->push($setting);
                }
            } else {
                $defaultSetting->field->value = $defaultSetting->value;
                $settings->push($defaultSetting);
            }
        }

        return $settings;
    }
}
