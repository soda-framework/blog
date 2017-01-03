<?php

namespace Soda\Blog\Console;

use Illuminate\Console\Command;

class Migrate extends Command
{

    protected $signature = 'soda:blog:migrate';
    protected $description = 'Migrate the Soda Blog Database';

    /**
     * Runs all database migrations for Soda Reports
     */
    public function handle()
    {
        $this->call('migrate', [
            '--path' => '/vendor/soda-framework/blog/migrations',
        ]);
    }
}
