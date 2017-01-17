<?php

namespace Soda\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Soda\Cms\Database\Support\Models\Traits\OptionallyBoundToApplication;

class Blog extends Model
{
    use SoftDeletes, OptionallyBoundToApplication;
    public $table = 'blog';
    public $fillable = [
        'name',
        'slug',
        'single_view',
        'list_view',
        'rss_enabled',
        'rss_slug',
        'rss_view',
        'rss_strip_tags',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function postDefaultSettings()
    {
        return $this->hasMany(PostDefaultSetting::class);
    }
}
