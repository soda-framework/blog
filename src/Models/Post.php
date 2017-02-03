<?php

namespace Soda\Blog\Models;

use Soda\Cms\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
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
        return $this->belongsToMany(Tag::class, 'blog_post_tag', 'post_id', 'tag_id')->withTimestamps();
    }

    public function scopeFromBlog($query, $id)
    {
        return $query->where('blog_id', $id);
    }

    public function scopeSearchText($q, $searchQuery)
    {
        return $q->where(function ($sq) use ($searchQuery) {
            $sq->whereRaw('MATCH(name,singletags,content) AGAINST (? IN NATURAL LANGUAGE MODE)', [$searchQuery])->orWhere('name', 'LIKE', "%$searchQuery%");
        });
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

    public function getPublishDateAttribute()
    {
        return $this->published_at ? $this->published_at : $this->created_at;
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

    public function getExcerpt($numCharacters = null, $words = true, $stripTags = true)
    {
        $excerpt = $this->content;

        // Remove HTML tags
        if ($stripTags) {
            $excerpt = strip_tags($excerpt);
        }

        if ($numCharacters) {
            if ($words) {
                // Reduce to word count
                $excerptWords = explode(' ', $excerpt);
                if (count($excerptWords) > $numCharacters) {
                    $excerpt = implode(' ', array_slice($excerptWords, 0, $numCharacters));
                }
            } else {
                // Reduce to character count
                if (strlen($excerpt) > $numCharacters) {
                    $excerpt = substr($excerpt, 0, $numCharacters);
                }
            }
        }

        return $excerpt;
    }

    public function getPublishDate() {
        return $this->published_at->setTimezone(config('soda.blog.publish_timezone'));
    }
}
