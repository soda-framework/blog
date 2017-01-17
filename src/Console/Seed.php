<?php

namespace Soda\Blog\Console;

use Illuminate\Console\Command;

class Seed extends Command
{
    protected $signature = 'soda:blog:seed';
    protected $description = 'Seed the Soda Blog Database';

    /**
     * Runs seeds for Soda Reports.
     */
    public function handle()
    {
        $this->call('db:seed', [
            '--class' => 'Soda\\Blog\\Support\\Seeder',
        ]);
    }
}
