<?php

namespace App\Listeners;

use App\Events\RequestStatusChanged;
use App\Models\RequestAudit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;

class LogRequestAudit implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }

    public function handle(RequestStatusChanged $event): void
    {
        if (!config('features.audit_log', true)) {
            return;
        }

        RequestAudit::create([
            'request_id' => $event->request->id,
            'user_id' => $event->userId,
            'action' => $event->action,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
            'old_assigned_to' => $event->oldAssignedTo,
            'new_assigned_to' => $event->newAssignedTo,
            'created_at' => now(),
        ]);
    }
}
