<?php

namespace App\Console\Commands;

use App\Domains\User\Models\UserModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class IgorTestCommand extends Command
{
    protected $signature = 'igor:test';

    protected $description = 'Command description';

    public function handle(): void
    {
        UserModel::create([
            'name'     => 'Igor Yuzkiv',
            'email'    => 'iy@crmoz.com',
            'password' => Hash::make('qwert123'),
        ]);
    }
}
