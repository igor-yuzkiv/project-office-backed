<?php

namespace App\Console\Commands;

use App\Domains\Project\Models\ProjectModel;
use Illuminate\Console\Command;

class IgorTestCommand extends Command
{
    protected $signature = 'igor:test';

    protected $description = 'Command description';

    public function handle(): void
    {
        $filters = [
            [
                'filter_key' => 'text',
                'field_name' => 'name',
                'value'      => 'test',
                'matchMode'  => 'contains',
                'params'     => [],
            ],
        ];

        $projects = ProjectModel::query()->filter($filters)->get();

        dump($projects->toArray());
    }
}
