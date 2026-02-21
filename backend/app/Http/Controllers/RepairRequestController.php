<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\RequestStatusChanged;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class RepairRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = RepairRequest::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($user && $user->role === 'master') {
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

        event(new RequestStatusChanged($repairRequest, 'created'));

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

            event(new RequestStatusChanged($repairRequest, 'assigned', $oldStatus, 'assigned', $oldAssignedTo, $master->id));
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

            event(new RequestStatusChanged($repairRequest, 'canceled', $oldStatus, 'canceled'));
        });

        return response()->json($repairRequest);
    }

    public function take(RepairRequest $repairRequest)
    {
        $this->authorize('take', $repairRequest);

        if ($repairRequest->status !== 'assigned') {
            return response()->json(['message' => 'Request already taken or not available'], 409);
        }

        $updated = RepairRequest::where('id', $repairRequest->id)
            ->where('status', 'assigned')
            ->where('assignedTo', Auth::id())
            ->update(['status' => 'in_progress', 'updated_at' => now()]);

        if ($updated === 0) {
            return response()->json(['message' => 'Request cannot be taken or already taken'], 409);
        }

        $repairRequest->refresh();
        event(new RequestStatusChanged($repairRequest, 'taken', 'assigned', 'in_progress'));

        return response()->json($repairRequest);
    }

    public function complete(RepairRequest $repairRequest)
    {
        $this->authorize('complete', $repairRequest);

        DB::transaction(function () use ($repairRequest) {
            $oldStatus = $repairRequest->status;
            $repairRequest->status = 'done';
            $repairRequest->save();

            event(new RequestStatusChanged($repairRequest, 'completed', $oldStatus, 'done'));
        });

        return response()->json($repairRequest);
    }
}
