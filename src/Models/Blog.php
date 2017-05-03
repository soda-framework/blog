<?php

namespace Soda\Blog\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Soda\Cms\Database\Models\Traits\OptionallyBoundToApplication;

class Blog extends Model
{
    use SoftDeletes, OptionallyBoundToApplication;
    public $table = 'blog';
    public $fillable = [
        'application_id',
        'single_view',
        'list_view',
        'rss_view',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getSetting($name)
    {
        return app('soda')->getApplication()->getSetting('blog_' . $name);
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
