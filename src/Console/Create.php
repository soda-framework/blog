<?php

namespace Soda\Blog\Console;

use Illuminate\Console\Command;
use Soda\Blog\Models\Blog;
use Soda\Cms\Database\Models\Application;
use Soda\Cms\Database\Models\ApplicationSetting;

class Create extends Command
{
    protected $signature = 'soda:blog:create';
    protected $description = 'Creates a new blog for a given application';

    /**
     * Runs seeds for Soda Reports.
     */
    public function handle()
    {
        $applications = Application::with('urls')->get(['id', 'name']);

        if (!$applications) {
            $this->error('No applications found.');

            return;
        }

        $applicationChoices = $applications->map(function ($application) {
            return [
                'id'      => $application->id,
                'name'    => $application->name,
                'domains' => $application->urls->implode('domain', PHP_EOL),
            ];
        })->toArray();

        $this->table(['Id', 'Name', 'Domains'], $applicationChoices);

        do {
            $applicationChoice = $this->ask('Which application would you like to create a blog for? (enter ID)', $applications->first()->id);

            if (!in_array($applicationChoice, $applications->pluck('id')->toArray())) {
                $this->error('No application with ID '.$applicationChoice);
            }
        } while (!in_array($applicationChoice, $applications->pluck('id')->toArray()));

        $application = $applications->where('id', $applicationChoice)->first();
        $existingBlog = Blog::withoutGlobalScope('in-application')->firstOrNew(['application_id' => $application->id]);

        if ($existingBlog->id) {
            $this->error('Application "'.$application->name.'" already has a blog!');

            return;
        }

        $blogName = $this->ask('Enter a name for your blog', $application->name.' Blog');
        $blogSlug = $this->ask('Enter a slug for your blog', '/blog');

        $rssEnabled = $this->confirm('Enable RSS for blog?', true);
        $rssSlug = $rssEnabled ? $this->ask('Enter a slug for your blog RSS feed', 'rss') : '';
        $rssStripTags = $rssEnabled ? $this->confirm('Should HTML be stripped from RSS output?', true) : true;

        Blog::create([
            'application_id' => $application->id,
        ]);

        ApplicationSetting::withoutGlobalScope('in-application')->firstOrNew([
            'field_name'     => 'blog_name',
            'category'       => 'Blog',
            'application_id' => $application->id,
        ])->fill([
            'name'       => 'Blog name',
            'field_type' => 'text',
            'value'      => $blogName,
        ])->save();

        ApplicationSetting::withoutGlobalScope('in-application')->firstOrNew([
            'field_name'     => 'blog_slug',
            'category'       => 'Blog',
            'application_id' => $application->id,
        ])->fill([
            'name'       => 'Blog slug',
            'field_type' => 'text',
            'value'      => $blogSlug,
        ])->save();

        ApplicationSetting::withoutGlobalScope('in-application')->firstOrNew([
            'field_name'     => 'blog_rss_enabled',
            'category'       => 'Blog',
            'application_id' => $application->id,
        ])->fill([
            'name'       => 'RSS feed enabled',
            'field_type' => 'toggle',
            'value'      => $rssEnabled,
        ])->save();

        ApplicationSetting::withoutGlobalScope('in-application')->firstOrNew([
            'field_name'     => 'blog_rss_slug',
            'category'       => 'Blog',
            'application_id' => $application->id,
        ])->fill([
            'name'        => 'RSS feed slug',
            'field_type'  => 'text',
            'description' => 'URL to access RSS feed. Appended to base slug defined above.',
            'value'       => trim($rssSlug, '/'),
        ])->save();

        ApplicationSetting::withoutGlobalScope('in-application')->firstOrNew([
            'field_name'     => 'blog_rss_strip_tags',
            'category'       => 'Blog',
            'application_id' => $application->id,
        ])->fill([
            'name'        => 'Strip HTML tags from RSS',
            'field_type'  => 'toggle',
            'description' => 'Strip HTML from RSS output',
            'value'       => $rssStripTags,
        ])->save();

        $this->info('Blog "'.$blogName.'" created!');
    }
}
