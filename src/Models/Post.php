<?php

namespace Soda\Blog\Models;

use Carbon\Carbon;
use Soda\Cms\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Soda\Cms\Models\Traits\HasMediaTrait;
use Soda\Cms\Models\Traits\DraftableTrait;
use Soda\Cms\Models\Traits\SluggableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Soda\Blog\Models\Traits\BlogSortableTrait;

class Post extends Model
{
    use SoftDeletes, BlogSortableTrait, DraftableTrait, SluggableTrait, HasMediaTrait;

    public $table = 'blog_posts';
    public $fillable = [
        'name',
        'content',
        'excerpt',
        'slug',
        'singletags',
        'blog_id',
        'status',
        'featured_image',
        'published_at',
        'user_id',
    ];
    protected $dates = ['publish_date', 'published_at', 'deleted_at'];
    protected static $sortableGroupField = 'blog_id';
    protected static $publishDateField = 'published_at';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->next()->decrement('position');
        });

        static::addGlobalScope('publish_date', function (Builder $builder) {
            $table = $builder->getModel()->getTable();

            return $builder->addSelect("$table.*", \DB::raw("CASE WHEN `$table`.`published_at` IS NULL THEN `$table`.`created_at` ELSE `$table`.`published_at` END publish_date"));
        });
    }

    public function settings()
    {
        return $this->hasMany(PostSetting::class, 'post_id');
    }

    public function defaultSettings()
    {
        return $this->hasMany(PostDefaultSetting::class, 'blog_id', 'blog_id');
    }

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'blog_post_tag')->withTimestamps();
    }

    public function scopeFromBlog($query, $id)
    {
        return $query->where('blog_id', $id);
    }

    public function scopeSearchText($q, $searchQuery)
    {
        return $q->whereRaw('MATCH(name,singletags,content) AGAINST (? IN NATURAL LANGUAGE MODE)', [$searchQuery]);
    }

    public function getSetting($settingName)
    {
        $settings = $this->settings->filter(function ($setting) use ($settingName) {
            return $setting->name === $settingName;
        });

        if (count($settings) > 1) {
            return $settings->pluck('value');
        } elseif (count($settings)) {
            return $settings->first()->value;
        }
    }

    public function scopeRelated($q, $relatedId, $relatedOnly = true)
    {
        $postTable = $this->getTable();
        $tagsTable = $this->tags()->getTable();

        $q->addSelect("$postTable.*", DB::raw('COUNT(`relatedTags`.`post_id`) as matched_tags'))
            ->join("$tagsTable as postTags", function ($join) use ($postTable) {
                $join->on('postTags.post_id', '=', "$postTable.id");
            })
            ->join("$tagsTable as relatedTags", function ($join) {
                $join->on('relatedTags.tag_id', '=', 'postTags.tag_id')->on('postTags.post_id', '!=', 'relatedTags.post_id');
            })
            ->where('relatedTags.post_id', $relatedId)
            ->where("$postTable.id", '!=', $relatedId)
            ->groupBy("$postTable.id")
            ->orderBy('matched_tags', 'DESC');

        if ($relatedOnly) {
            $q->having('matched_tags', '>', 0);
        }

        return $q;
    }

    public function getRelatedQuery($relatedOnly = true)
    {
        return (new static)->related($this->id);
    }

    public function getTitle()
    {
        return $this->name;
    }

    public function getAuthorName()
    {
        return isset($this->author) ? $this->author->name : null;
    }

    public function getPublishDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : ($this->published_at ? $this->published_at : $this->created_at);
    }

    public function getFullUrl()
    {
        $currentBlog = app('CurrentBlog');

        if ($currentBlog->id == $this->blog_id) {
            return URL::to($currentBlog->slug.'/'.trim($this->slug, '/'));
        }

        return URL::to($this->blog->slug.'/'.trim($this->slug, '/'));
    }

    public function getPreviousPost()
    {
        $query = static::where('id', '!=', $this->id);

        foreach ((array) config('soda.blog.default_sort') as $sortableField => $direction) {
            $query->where($sortableField, strtolower($direction) == 'DESC' ? '<=' : '>=', $this->$sortableField);

            break;
        }

        return $query->first();
    }

    public function getNextPost()
    {
        $query = static::reverseOrder()->where('id', '!=', $this->id);

        foreach ((array) config('soda.blog.default_sort') as $sortableField => $direction) {
            $query->where($sortableField, strtolower($direction) == 'DESC' ? '>=' : '<=', $this->$sortableField);

            break;
        }

        return $query->first();
    }
}
