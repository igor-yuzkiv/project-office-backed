<?php

namespace Database\Seeders;

use App\Domains\User\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Deterministic dataset for Playwright e2e runs.
 *
 * Kept intentionally minimal (a single known user) for the smoke-login
 * iteration. Extend here as new e2e scenarios need fixture data — credentials
 * are mirrored by the E2E_USER_* variables in .env.e2e.
 */
class E2eSeeder extends Seeder
{
    public function run(): void
    {
        UserModel::updateOrCreate(
            ['email' => 'e2e@example.com'],
            [
                'name'              => 'E2E User',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
            ],
        );
    }
}
