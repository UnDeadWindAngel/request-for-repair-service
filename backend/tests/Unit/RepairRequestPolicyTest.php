<?php

namespace Tests\Unit;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairRequestPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected $dispatcher;
    protected $master1;
    protected $master2;
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = User::factory()->create(['role' => 'dispatcher']);
        $this->master1 = User::factory()->create(['role' => 'master']);
        $this->master2 = User::factory()->create(['role' => 'master']);
        $this->request = RepairRequest::factory()->create(['assignedTo' => $this->master1->id]);
    }

    /** @test */
    public function dispatcher_can_view_any_request()
    {
        $this->assertTrue($this->dispatcher->can('view', $this->request));
    }

    /** @test */
    public function master_can_view_own_request()
    {
        $this->assertTrue($this->master1->can('view', $this->request));
    }

    /** @test */
    public function master_cannot_view_others_request()
    {
        $this->assertFalse($this->master2->can('view', $this->request));
    }

    /** @test */
    public function only_dispatcher_can_assign()
    {
        $this->assertTrue($this->dispatcher->can('assign', $this->request));
        $this->assertFalse($this->master1->can('assign', $this->request));
    }

    /** @test */
    public function only_dispatcher_can_cancel()
    {
        $this->assertTrue($this->dispatcher->can('cancel', $this->request));
        $this->assertFalse($this->master1->can('cancel', $this->request));
    }

    /** @test */
    public function master_can_take_own_assigned_request()
    {
        $request = RepairRequest::factory()->create([
            'assignedTo' => $this->master1->id,
            'status' => 'assigned'
        ]);
        $this->assertTrue($this->master1->can('take', $request));
    }

    /** @test */
    public function master_cannot_take_own_request_if_not_assigned()
    {
        $request = RepairRequest::factory()->create([
            'assignedTo' => $this->master1->id,
            'status' => 'new'
        ]);
        $this->assertFalse($this->master1->can('take', $request));
    }

    /** @test */
    public function master_cannot_take_others_request()
    {
        $request = RepairRequest::factory()->create([
            'assignedTo' => $this->master2->id,
            'status' => 'assigned'
        ]);
        $this->assertFalse($this->master1->can('take', $request));
    }

    /** @test */
    public function master_can_complete_own_in_progress_request()
    {
        $request = RepairRequest::factory()->create([
            'assignedTo' => $this->master1->id,
            'status' => 'in_progress'
        ]);
        $this->assertTrue($this->master1->can('complete', $request));
    }

    /** @test */
    public function master_cannot_complete_others_request()
    {
        $request = RepairRequest::factory()->create([
            'assignedTo' => $this->master2->id,
            'status' => 'in_progress'
        ]);
        $this->assertFalse($this->master1->can('complete', $request));
    }
}
