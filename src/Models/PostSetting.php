<?php

namespace Soda\Blog\Models;

use Soda\Cms\Models\Field;
use Illuminate\Database\Eloquent\Model;

class PostSetting extends Model
{
    public $table = 'blog_post_settings';
    public $fillable = ['name', 'value', 'field_id'];
    protected $dates = ['created_at', 'updated_at'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
