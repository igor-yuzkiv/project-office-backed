<?php

namespace App\Domains\ProjectDocument\Enums;

enum ProjectDocumentStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Active = 'active';
    case Deprecated = 'deprecated';
    case Archived = 'archived';
}
