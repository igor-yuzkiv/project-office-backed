<?php

namespace App\Libs\AuditTrail\Facades;

use App\Libs\AuditTrail\AuditRecorder;
use App\Libs\AuditTrail\Contracts\AuditRecord;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void capture(AuditRecord $record)
 *
 * @see AuditRecorder
 */
class AuditTrail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AuditRecorder::class;
    }
}
