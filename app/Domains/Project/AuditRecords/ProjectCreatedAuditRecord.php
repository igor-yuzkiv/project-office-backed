<?php

namespace App\Domains\Project\AuditRecords;

use App\Domains\Project\Models\ProjectModel;
use App\Libs\AuditTrail\Concerns\DescribesSubject;
use App\Libs\AuditTrail\Concerns\ResolvesActorName;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Database\Eloquent\Model;

class ProjectCreatedAuditRecord implements AuditRecord
{
    use DescribesSubject, ResolvesActorName;

    public function __construct(private readonly ProjectModel $project) {}

    public function type(): string
    {
        return 'project.created';
    }

    public function title(): string
    {
        return "{$this->actorName()} created project {$this->subjectName($this->project)}";
    }

    public function description(): ?string
    {
        return $this->project->prefix;
    }

    public function subject(): ?Model
    {
        return $this->project;
    }
}
