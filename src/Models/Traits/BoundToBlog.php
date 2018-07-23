<?php

namespace Soda\Blog\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BoundToBlog
{
    /**
     * Adds application_id global scope filter to model.
     */
    public static function bootBoundToBlog()
    {
        static::addGlobalScope('in-blog', function (Builder $builder) {
            return $builder->where('blog_id', '=', app('CurrentBlog')->getKey());
        });
    }
}
