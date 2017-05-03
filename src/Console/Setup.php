<?php

namespace Soda\Blog\Console;

use Illuminate\Console\Command;

class Setup extends Command
{
    protected $signature = 'soda:blog:setup';
    protected $description = 'Set up the Soda Blog Database';

    /**
     * Runs seeds for Soda Reports.
     */
    public function handle()
    {
        $this->call('db:seed', [
            '--class' => 'Soda\\Blog\\Support\\Setup',
        ]);
    }
}
