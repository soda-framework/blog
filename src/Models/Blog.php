<?php

namespace Soda\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Soda\Cms\Models\Traits\OptionallyInApplicationTrait;

class Blog extends Model
{
    use SoftDeletes, OptionallyInApplicationTrait;
    public $table = 'blog';
    public $fillable = ['name', 'text', 'slug'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function postDefaultSettings()
    {
        return $this->hasMany(BlogPostSettingsDefault::class);
    }
}
