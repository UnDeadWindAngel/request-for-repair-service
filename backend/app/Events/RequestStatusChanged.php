<?php

namespace App\Events;

use App\Models\RepairRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $action;
    public $oldStatus;
    public $newStatus;
    public $oldAssignedTo;
    public $newAssignedTo;
    public $userId;

    public function __construct(RepairRequest $request, $action, $oldStatus = null, $newStatus = null, $oldAssignedTo = null, $newAssignedTo = null)
    {
        $this->request = $request;
        $this->action = $action;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus ?? $request->status;
        $this->oldAssignedTo = $oldAssignedTo;
        $this->newAssignedTo = $newAssignedTo ?? $request->assignedTo;
        $this->userId = auth()->id();
    }
}
