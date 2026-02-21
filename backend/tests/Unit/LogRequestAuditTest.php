<?php

namespace Tests\Unit;

use App\Events\RequestStatusChanged;
use App\Listeners\LogRequestAudit;
use App\Models\RepairRequest;
use App\Models\RequestAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogRequestAuditTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_logs_audit_when_event_is_handled()
    {
        $user = User::factory()->create();
        $request = RepairRequest::factory()->create();

        $event = new RequestStatusChanged($request, 'created', null, 'new', null, null);
        $event->userId = $user->id;

        $listener = new LogRequestAudit();
        $listener->handle($event);

        $this->assertDatabaseHas('request_audits', [
            'request_id' => $request->id,
            'user_id' => $user->id,
            'action' => 'created',
            'new_status' => 'new',
            'old_status' => null,
            'old_assigned_to' => null,
            'new_assigned_to' => null,
        ]);
    }

    /** @test */
    public function it_does_not_log_if_feature_disabled()
    {
        config(['features.audit_log' => false]);

        $user = User::factory()->create();
        $request = RepairRequest::factory()->create();

        $event = new RequestStatusChanged($request, 'created', null, 'new', null, null);
        $event->userId = $user->id;

        $listener = new LogRequestAudit();
        $listener->handle($event);

        $this->assertDatabaseMissing('request_audits', [
            'request_id' => $request->id,
        ]);
    }
}
