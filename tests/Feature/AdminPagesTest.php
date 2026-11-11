<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_database_pages_render_without_demo_fixtures(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        foreach ([
            'admin.dashboard', 'admin.users', 'admin.roles', 'admin.categories', 'admin.transactions',
            'admin.reports', 'admin.backup', 'admin.settings', 'admin.notifications', 'admin.profile', 'admin.messages',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_admin_can_create_category_and_close_registration(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('admin.categories.store'), ['name' => 'Local Spices'])->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Local Spices', 'slug' => 'local-spices', 'is_active' => true]);

        $this->post(route('admin.settings.save'), [
            'site_name' => 'FarmDirect',
            'support_email' => 'support@example.test',
            'support_phone' => '01700000000',
            'delivery_charge' => 50,
            'free_delivery_above' => 1200,
            'cod_enabled' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'allow_registration', 'value' => '0']);

        $this->post(route('logout'));
        $this->get(route('signup'))->assertForbidden();
        $this->post(route('signup.store'), [])->assertForbidden();
    }
}