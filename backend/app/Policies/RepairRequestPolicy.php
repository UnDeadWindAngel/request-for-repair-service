<?php

namespace App\Policies;

use App\Models\RepairRequest;
use App\Models\User;

class RepairRequestPolicy
{
    public function view(User $user, RepairRequest $repairRequest)
    {
        if ($user->role === 'dispatcher') {
            return true;
        }
        if ($user->role === 'master') {
            return $repairRequest->assignedTo === $user->id;
        }
        return false;
    }

    public function create(User $user)
    {
        return true; // разрешаем всем аутентифицированным
    }

    public function assign(User $user, RepairRequest $repairRequest)
    {
        return $user->role === 'dispatcher' && !in_array($repairRequest->status, ['canceled', 'done']);
    }

    public function cancel(User $user, RepairRequest $repairRequest)
    {
        return $user->role === 'dispatcher' && !in_array($repairRequest->status, ['done', 'canceled']);
    }

    public function take(User $user, RepairRequest $repairRequest)
    {
        return $user->role === 'master'
            && $repairRequest->assignedTo === $user->id
            && $repairRequest->status === 'assigned';
    }

    public function complete(User $user, RepairRequest $repairRequest)
    {
        return $user->role === 'master'
            && $repairRequest->assignedTo === $user->id
            && $repairRequest->status === 'in_progress';
    }

    public function update(User $user, RepairRequest $repairRequest)
    {
        return false;
    }

    public function delete(User $user, RepairRequest $repairRequest)
    {
        return false;
    }
}
