<?php

namespace Soda\Blog\Console;

use Illuminate\Console\Command;

class Install extends Command
{
    protected $signature = 'soda:blog:install';
    protected $description = 'Install the Soda Blog module';

    /**
     * Runs seeds for Soda Reports.
     */
    public function handle()
    {
        $this->call('migrate', [
            '--path' => '/vendor/soda-framework/blog/migrations',
        ]);

        $this->call('db:seed', [
            '--class' => 'Soda\\Blog\\Support\\InstallPermissions',
        ]);
    }
}
