<?php

namespace Database\Seeders;

use App\Domains\Project\Models\ProjectModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        ProjectModel::factory(15)->create();
    }
}
