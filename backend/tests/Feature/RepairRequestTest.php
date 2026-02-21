<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RepairRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $dispatcher;
    protected $master1;
    protected $master2;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаём пользователей
        $this->dispatcher = User::factory()->create(['role' => 'dispatcher']);
        $this->master1 = User::factory()->create(['role' => 'master']);
        $this->master2 = User::factory()->create(['role' => 'master']);
    }

    #[Test]
    public function dispatcher_can_create_request()
    {
        $this->actingAs($this->dispatcher);

        $data = [
            'clientName' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'problemText' => $this->faker->sentence,
        ];

        $response = $this->postJson('/api/requests', $data);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'new')
            ->assertJsonPath('clientName', $data['clientName']);
    }

    #[Test]
    public function master_cannot_assign_request()
    {
        $this->actingAs($this->master1);
        $request = RepairRequest::factory()->create(['status' => 'new']);

        $response = $this->postJson("/api/requests/{$request->id}/assign", [
            'master_id' => $this->master2->id
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function dispatcher_can_assign_master()
    {
        $this->actingAs($this->dispatcher);
        $request = RepairRequest::factory()->create(['status' => 'new']);

        $response = $this->postJson("/api/requests/{$request->id}/assign", [
            'master_id' => $this->master1->id
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'assigned')
            ->assertJsonPath('assignedTo', $this->master1->id);
    }

    #[Test]
    public function dispatcher_can_cancel_request()
    {
        $this->actingAs($this->dispatcher);
        $request = RepairRequest::factory()->create(['status' => 'new']);

        $response = $this->postJson("/api/requests/{$request->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'canceled');
    }

    #[Test]
    public function master_can_take_assigned_request()
    {
        $this->actingAs($this->master1);
        $request = RepairRequest::factory()->create([
            'status' => 'assigned',
            'assignedTo' => $this->master1->id
        ]);

        $response = $this->postJson("/api/requests/{$request->id}/take");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'in_progress');
    }

    #[Test]
    public function master_cannot_take_request_assigned_to_another_master()
    {
        $this->actingAs($this->master1);
        $request = RepairRequest::factory()->create([
            'status' => 'assigned',
            'assignedTo' => $this->master2->id
        ]);

        $response = $this->postJson("/api/requests/{$request->id}/take");

        $response->assertStatus(403);
    }

    #[Test]
    public function master_cannot_take_request_not_in_assigned_status()
    {
        $this->actingAs($this->master1);
        $request = RepairRequest::factory()->create([
            'status' => 'new',
            'assignedTo' => $this->master1->id
        ]);

        $response = $this->postJson("/api/requests/{$request->id}/take");

        $response->assertStatus(409);
    }

    #[Test]
    public function race_condition_on_take_returns_409_for_second_request()
    {
        $this->actingAs($this->master1);
        $request = RepairRequest::factory()->create([
            'status' => 'assigned',
            'assignedTo' => $this->master1->id
        ]);

        // Первый запрос
        $response1 = $this->postJson("/api/requests/{$request->id}/take");
        $response1->assertStatus(200);

        // Второй запрос (должен быть 409)
        $response2 = $this->postJson("/api/requests/{$request->id}/take");
        $response2->assertStatus(409);
    }

    #[Test]
    public function master_can_complete_in_progress_request()
    {
        $this->actingAs($this->master1);
        $request = RepairRequest::factory()->create([
            'status' => 'in_progress',
            'assignedTo' => $this->master1->id
        ]);

        $response = $this->postJson("/api/requests/{$request->id}/complete");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'done');
    }

    #[Test]
    public function test_index_filters_by_status_for_dispatcher()
    {
        $this->actingAs($this->dispatcher);
        RepairRequest::factory()->count(3)->create(['status' => 'new']);
        RepairRequest::factory()->count(2)->create(['status' => 'assigned']);

        $response = $this->getJson('/api/requests?status=new');
        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    #[Test]
    public function master_sees_only_own_requests()
    {
        $this->actingAs($this->master1);
        RepairRequest::factory()->create(['assignedTo' => $this->master1->id]);
        RepairRequest::factory()->create(['assignedTo' => $this->master2->id]);

        $response = $this->getJson('/api/requests');
        $response->assertStatus(200)
            ->assertJsonCount(1);
    }
}
