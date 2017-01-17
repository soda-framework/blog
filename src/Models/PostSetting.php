<?php

namespace Soda\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostSetting extends Model
{
    use SoftDeletes;

    public $table = 'blog_post_settings';
    public $fillable = ['name', 'value', 'field_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function field()
    {
        return $this->belongsTo(resolve_class('soda.field.model'));
    }
}
