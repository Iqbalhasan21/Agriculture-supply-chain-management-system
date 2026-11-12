<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\Division;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private District $district;

    protected function setUp(): void
    {
        parent::setUp();

        $division = Division::create(['name' => 'Dhaka', 'bn_name' => 'ঢাকা']);
        $this->district = District::create([
            'division_id' => $division->id,
            'name' => 'Dhaka',
            'bn_name' => 'ঢাকা',
            'latitude' => 23.81,
            'longitude' => 90.41,
        ]);
    }

    public function test_client_can_register_and_login_with_nid_without_email(): void
    {
        $this->post(route('signup.store'), [
            'account_type' => 'client',
            'name' => 'Client Test',
            'phone' => '01700000000',
            'nid_number' => '1234567890',
            'district' => $this->district->name,
            'upazila' => 'Dhanmondi',
            'address' => 'Road 1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('client.dashboard'));

        $user = User::where('nid_hash', User::nidHashFor('1234567890'))->firstOrFail();
        $this->assertNull($user->email);
        $this->assertSame('Dhanmondi', $user->upazila);
        $this->assertStringNotContainsString('1234567890', $user->nid_hash);

        $this->post(route('logout'));
        $this->post(route('login.attempt'), [
            'identifier' => '1234567890',
            'password' => 'password123',
            'role' => 'client',
        ])->assertRedirect(route('client.dashboard'));
    }

    public function test_farmer_registration_persists_upazila_separately(): void
    {
        $this->post(route('signup.store'), [
            'account_type' => 'farmer',
            'name' => 'Farmer Test',
            'phone' => '01700000001',
            'nid_number' => '1234567890123',
            'farm' => 'Green Farm',
            'district' => $this->district->name,
            'upazila' => 'Savar',
            'address' => 'Village Road',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('farmer.dashboard'));

        $farmer = Farmer::with('user')->firstOrFail();
        $this->assertSame('Savar', $farmer->upazila);
        $this->assertSame('Savar', $farmer->user->upazila);
        $this->assertNull($farmer->user->email);
    }

    public function test_nid_and_upazila_are_required(): void
    {
        $this->from(route('signup'))->post(route('signup.store'), [
            'account_type' => 'client',
            'name' => 'Client Test',
            'phone' => '01700000000',
            'district' => $this->district->name,
            'address' => 'Road 1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('signup'))
            ->assertSessionHasErrors(['nid_number', 'upazila']);
    }

    public function test_existing_email_accounts_can_still_login(): void
    {
        User::factory()->create([
            'email' => 'legacy@example.test',
            'nid_hash' => null,
            'password' => 'password123',
        ]);

        $this->post(route('login.attempt'), [
            'identifier' => 'legacy@example.test',
            'password' => 'password123',
            'role' => 'client',
        ])->assertRedirect(route('client.dashboard'));
    }
}
