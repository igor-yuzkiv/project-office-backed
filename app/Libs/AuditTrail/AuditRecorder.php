<?php

namespace App\Libs\AuditTrail;

use App\Libs\AuditTrail\Contracts\AuditRecord;
use App\Libs\AuditTrail\Models\AuditRecordModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AuditRecorder
{
    public function capture(AuditRecord $record): void
    {
        // Read once, inside the try: the catch block below must not call back into the record,
        // or a throwing type() would escape capture() from the very handler meant to contain it.
        $type = null;
        $subjectType = null;
        $subjectId = null;

        try {
            $type = $record->type();

            $subject = $record->subject();
            // An unsaved model has no key, and a half-filled subject resolves to nothing —
            // treat it as no subject at all.
            $subjectId = $subject?->getKey();
            if ($subjectId !== null && $subjectId !== '') {
                $subjectType = $subject->getMorphClass();
                $subjectId = (string) $subjectId;
            } else {
                $subjectId = null;
            }

            // Nested transaction: inside an open outer transaction this becomes a savepoint, so a
            // failed audit write rolls back to it instead of leaving the caller's transaction aborted.
            DB::transaction(function () use ($record, $type, $subjectType, $subjectId): void {
                AuditRecordModel::query()->create([
                    'id'           => (string) Str::ulid(),
                    'type'         => $type,
                    'title'        => $record->title(),
                    'description'  => $record->description(),
                    'subject_type' => $subjectType,
                    'subject_id'   => $subjectId,
                    'created_by'   => auth()->id(),
                    'created_at'   => now(),
                ]);
            });
        } catch (Throwable $exception) {
            // Auditing never fails the action it describes.
            Log::warning('Failed to capture an audit record.', [
                'type'      => $type,
                'subject'   => $subjectType === null ? null : ['type' => $subjectType, 'id' => $subjectId],
                'exception' => $exception,
            ]);
        }
    }
}
