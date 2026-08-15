<?php

namespace App\Libs\AuditTrail\Concerns;

trait ResolvesActorName
{
    /**
     * The name goes into the title as a snapshot of the moment: the record must stay readable
     * even after the user is renamed or deleted.
     */
    protected function actorName(): string
    {
        // Larastan types auth()->user() as non-null; at runtime it is null outside a request,
        // which is exactly the case this fallback exists for.
        $user = auth()->user();

        // @phpstan-ignore nullsafe.neverNull
        return $user?->name ?? 'Someone';
    }
}
