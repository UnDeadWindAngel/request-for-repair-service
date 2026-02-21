<?php

namespace Tests\Unit;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairRequestModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_scopes_requests_for_master()
    {
        $master = User::factory()->create(['role' => 'master']);
        $otherMaster = User::factory()->create(['role' => 'master']);

        RepairRequest::factory()->count(3)->create(['assignedTo' => $master->id]);
        RepairRequest::factory()->count(2)->create(['assignedTo' => $otherMaster->id]);

        $requests = RepairRequest::forMaster($master->id)->get();

        $this->assertCount(3, $requests);
    }
}
