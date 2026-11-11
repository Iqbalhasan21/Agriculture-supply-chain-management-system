<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\Division;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmerFollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_follow_and_unfollow_an_approved_farmer(): void
    {
        $division = Division::create(['name' => 'Dhaka']);
        $district = District::create(['division_id' => $division->id, 'name' => 'Dhaka']);
        $client = User::factory()->create(['role' => 'client']);
        $farmerUser = User::factory()->create(['role' => 'farmer']);
        $farmer = Farmer::create([
            'user_id' => $farmerUser->id,
            'farm_name' => 'Green Farm',
            'division_id' => $division->id,
            'district_id' => $district->id,
            'upazila' => 'Savar',
            'status' => 'approved',
        ]);

        $this->actingAs($client)->post(route('farmer.follow', $farmer->id))->assertRedirect();
        $this->assertDatabaseHas('farmer_follows', ['user_id' => $client->id, 'farmer_id' => $farmer->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $farmerUser->id]);

        $this->post(route('farmer.follow', $farmer->id))->assertRedirect();
        $this->assertDatabaseMissing('farmer_follows', ['user_id' => $client->id, 'farmer_id' => $farmer->id]);
    }
}