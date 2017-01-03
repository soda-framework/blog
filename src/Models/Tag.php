<?php
namespace Soda\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SoftDeletes;

    public $table = 'blog_tags';
    public $fillable = ['name'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'blog_post_tag')->withTimestamps();
    }
}
