<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\FarmDirectNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotificationAndContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_mark_their_own_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $other = User::factory()->create(['role' => 'client']);
        $user->notify(new FarmDirectNotification('Order updated', 'Your order changed.'));
        $other->notify(new FarmDirectNotification('Order updated', 'A different order changed.'));
        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)->post(route('notifications.read', $notification->id))->assertRedirect();

        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'notifiable_id' => $user->id]);
        $this->assertNotNull($user->notifications()->first()->fresh()->read_at);
        $this->assertNull($other->notifications()->first()->read_at);
        $this->actingAs($user)->get(route('client.notifications'))->assertOk();
    }

    public function test_admin_can_save_a_contact_reply(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $messageId = DB::table('contact_messages')->insertGetId([
            'name' => 'Customer',
            'email' => 'customer@example.test',
            'subject' => 'Delivery question',
            'message' => 'When will my order arrive?',
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.messages.reply', $messageId), [
            'admin_reply' => 'Your order is scheduled for tomorrow.',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('contact_messages', [
            'id' => $messageId,
            'admin_reply' => 'Your order is scheduled for tomorrow.',
            'replied_by' => $admin->id,
            'is_read' => true,
        ]);
        $this->actingAs($admin)->get(route('admin.messages'))->assertOk();
    }
}