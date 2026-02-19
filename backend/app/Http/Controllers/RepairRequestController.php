<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepairRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = RepairRequest::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($user->role === 'master') {
            $query->where('assignedTo', $user->id);
        }

        $requests = $query->with('assignedMaster')->get();

        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clientName' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'problemText' => 'required|string',
        ]);

        $repairRequest = RepairRequest::create([
            'clientName' => $validated['clientName'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'problemText' => $validated['problemText'],
            'status' => 'new',
            'assignedTo' => null,
        ]);

        $this->logAudit($repairRequest, 'created');

        return response()->json($repairRequest, 201);
    }

    public function show(RepairRequest $repairRequest)
    {
        $this->authorize('view', $repairRequest);
        return response()->json($repairRequest->load('assignedMaster'));
    }

    public function assign(Request $request, RepairRequest $repairRequest)
    {
        $this->authorize('assign', $repairRequest);

        $validated = $request->validate([
            'master_id' => 'required|exists:users,id',
        ]);

        $master = User::find($validated['master_id']);
        if ($master->role !== 'master') {
            return response()->json(['message' => 'User is not a master'], 422);
        }

        DB::transaction(function () use ($repairRequest, $master) {
            $oldAssignedTo = $repairRequest->assignedTo;
            $oldStatus = $repairRequest->status;

            $repairRequest->assignedTo = $master->id;
            $repairRequest->status = 'assigned';
            $repairRequest->save();

            $this->logAudit($repairRequest, 'assigned', $oldStatus, 'assigned', $oldAssignedTo, $master->id);
        });

        return response()->json($repairRequest);
    }

    public function cancel(RepairRequest $repairRequest)
    {
        $this->authorize('cancel', $repairRequest);

        DB::transaction(function () use ($repairRequest) {
            $oldStatus = $repairRequest->status;
            $repairRequest->status = 'canceled';
            $repairRequest->save();

            $this->logAudit($repairRequest, 'canceled', $oldStatus, 'canceled');
        });

        return response()->json($repairRequest);
    }

    public function take(RepairRequest $repairRequest)
    {
        $this->authorize('take', $repairRequest);

        $updated = RepairRequest::where('id', $repairRequest->id)
            ->where('status', 'assigned')
            ->where('assignedTo', Auth::id())
            ->update(['status' => 'in_progress', 'updated_at' => now()]);

        if ($updated === 0) {
            return response()->json(['message' => 'Request cannot be taken or already taken'], 409);
        }

        $repairRequest->refresh();
        $this->logAudit($repairRequest, 'taken', 'assigned', 'in_progress');

        return response()->json($repairRequest);
    }

    public function complete(RepairRequest $repairRequest)
    {
        $this->authorize('complete', $repairRequest);

        DB::transaction(function () use ($repairRequest) {
            $oldStatus = $repairRequest->status;
            $repairRequest->status = 'done';
            $repairRequest->save();

            $this->logAudit($repairRequest, 'completed', $oldStatus, 'done');
        });

        return response()->json($repairRequest);
    }

    private function logAudit($request, $action, $oldStatus = null, $newStatus = null, $oldAssignedTo = null, $newAssignedTo = null)
    {
        if (!config('features.audit_log', true)) {
            return;
        }

        \App\Models\RequestAudit::create([
            'request_id' => $request->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus ?? $request->status,
            'old_assigned_to' => $oldAssignedTo,
            'new_assigned_to' => $newAssignedTo ?? $request->assignedTo,
            'created_at' => now(),
        ]);
    }
}
