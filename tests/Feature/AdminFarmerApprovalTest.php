<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\Division;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFarmerApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_approval_activates_the_farmer_account(): void
    {
        $division = Division::create(['name' => 'Dhaka']);
        $district = District::create(['division_id' => $division->id, 'name' => 'Dhaka']);
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'farmer', 'is_active' => false]);
        $farmer = Farmer::create([
            'user_id' => $user->id,
            'farm_name' => 'Green Farm',
            'division_id' => $division->id,
            'district_id' => $district->id,
            'upazila' => 'Savar',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.farmers.approve', $farmer->id))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('farmers', ['id' => $farmer->id, 'status' => 'approved', 'is_verified' => true]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => true]);
    }

    public function test_admin_rejection_persists_reason_and_keeps_account_inactive(): void
    {
        $division = Division::create(['name' => 'Dhaka']);
        $district = District::create(['division_id' => $division->id, 'name' => 'Dhaka']);
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'farmer', 'is_active' => false]);
        $farmer = Farmer::create([
            'user_id' => $user->id,
            'farm_name' => 'Green Farm',
            'division_id' => $division->id,
            'district_id' => $district->id,
            'upazila' => 'Savar',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.farmers.reject', $farmer->id), ['reason' => 'Please provide farm ownership details.'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('farmers', [
            'id' => $farmer->id,
            'status' => 'rejected',
            'review_note' => 'Please provide farm ownership details.',
        ]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
    }
}