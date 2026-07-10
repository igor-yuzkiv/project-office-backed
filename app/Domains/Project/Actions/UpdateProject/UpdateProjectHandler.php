<?php

namespace App\Domains\Project\Actions\UpdateProject;

use App\Domains\Project\Models\ProjectModel;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProjectHandler
{
    use AsAction;

    public function handle(UpdateProjectCommand $command): ProjectModel
    {
        // Optional fields (null = not provided, skip update)
        $data = array_filter(
            [
                'name'   => $command->name,
                'status' => $command->status,
            ],
            fn ($value) => $value !== null
        );

        // Nullable fields (null = clear the value)
        $data['description'] = $command->description;
        $data['start_date'] = $command->startDate;
        $data['end_date'] = $command->endDate;

        $command->project->update($data);

        if ($command->tagIds !== null) {
            $command->project->tags()->sync($command->tagIds);
        }

        return $command->project->fresh();
    }
}
