<?php
namespace Soda\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Soda\Cms\Models\Field;

class PostDefaultSetting extends Model
{
    public $table = 'blog_post_default_settings';
    public $fillable = ['name', 'value', 'field_id', 'blog_id'];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
