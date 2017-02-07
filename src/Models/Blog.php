<?php

namespace Soda\Blog\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Soda\Cms\Models\Traits\OptionallyInApplicationTrait;

class Blog extends Model
{
    use SoftDeletes, OptionallyInApplicationTrait;
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

    public function getArchiveDates()
    {
        $timezone = new \DateTimeZone(config('soda.blog.publish_timezone'));
        $timezoneOffset = $timezone->getOffset(Carbon::now()) / 3600;
        $timezoneOffset = ($timezoneOffset > 0 ? '+'.$timezoneOffset : '-'.$timezoneOffset).':00';

        $postDates = $this->posts()->select(DB::raw('YEAR(CONVERT_TZ(published_at, "+00:00", "'.$timezoneOffset.'")) as year'), DB::raw('MONTH(CONVERT_TZ(published_at, "+00:00", "'.$timezoneOffset.'")) as month'))->groupBy('year')->groupBy('month')->get();

        return $postDates->sortByDesc('month')->groupBy('year')->sortByDesc('year')->map(function ($item) {
            return $item->map(function ($subItem) {
                return Carbon::createFromDate($subItem->year, $subItem->month)->startOfMonth();
            });
        });
    }
}
