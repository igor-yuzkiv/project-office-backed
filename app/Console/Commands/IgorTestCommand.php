<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class IgorTestCommand extends Command
{
    protected $signature = 'igor:test';

    protected $description = 'Command description';

    public function handle(): void {
        dump('Hello, Igor!');
    }
}
