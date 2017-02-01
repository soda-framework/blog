<?php

namespace Soda\Blog\Models;

use Soda\Cms\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Soda\Cms\Models\Traits\DraftableTrait;
use Soda\Cms\Models\Traits\SluggableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Soda\Blog\Models\Traits\BlogSortableTrait;

class Post extends Model
{
    use SoftDeletes, BlogSortableTrait, DraftableTrait, SluggableTrait;

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
    protected $dates = ['published_at', 'deleted_at'];
    protected static $sortableGroupField = 'blog_id';
    protected static $publishDateField = 'published_at';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->next()->decrement('position');
        });
    }

    public function settings()
    {
        return $this->hasMany(PostSetting::class);
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

    public function scopeOrderByPublishDate($q)
    {
        $table = $this->getTable();

        return $q->orderByRaw("CASE WHEN `$table`.`published_at` IS NULL THEN `$table`.`created_at` ELSE `$table`.`published_at` END DESC");
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

    public function getRelated($id = null, $limit = null)
    {
        $post = $id !== null ? static::find($id) : static::find($this->id);

        //OK, a bit of a nasty ass query to figure out similar blog posts based on tags.
        /*
        $related = DB::select(DB::raw('SELECT bpt.post_id, p.*, COUNT(post_id) as matched_tags
            FROM blog_post_tag bpt
            INNER JOIN
                (SELECT tag_id
                 FROM blog_post_tag
                 WHERE post_id = '.$post->id.') otags ON bpt.tag_id = otags.tag_id
            INNER JOIN blog_posts p ON bpt.post_id = p.id
            WHERE bpt.post_id <> '.$post->id.'
            AND deleted_at IS NULL
            GROUP BY bpt.post_id, p.name
            ORDER BY COUNT(post_id)'.($limit ? " LIMIT $limit" : "")));
        */

        $tagsTable = (new Tag)->getTable();
        $postTable = (new self)->getTable();

        $related = Tag::select("$tagsTable.post_id", "$postTable.*", DB::raw("COUNT($tagsTable.post_id) as matched_tags"))
            ->join("$tagsTable as otherTags", function ($join) use ($tagsTable, $post) {
                $join->on("$tagsTable.tag_id", '=', 'otherTags.tag_id')
                    ->where('otherTags.post_id', '=', $post->id);
            })
            ->join(self::class, "$tagsTable.post_id", '=', "$postTable.id")
            ->where("$tagsTable.post_id", '!=', $post->id)
            ->groupBy("$tagsTable.post_id", "$postTable.name")
            ->orderBy('matched_tags');

        if ($limit) {
            $related->take($limit);
        }

        return $related;
    }

    public function getTitle()
    {
        return $this->name;
    }

    public function getAuthorName()
    {
        return isset($this->author) ? $this->author->name : null;
    }
}
