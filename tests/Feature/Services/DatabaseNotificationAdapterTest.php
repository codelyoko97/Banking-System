<?php

namespace Tests\Feature\Services;

use App\Services\DatabaseNotificationAdapter;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseNotificationAdapterTest extends TestCase
{
    use RefreshDatabase;

    private DatabaseNotificationAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new DatabaseNotificationAdapter();
    }

    /** @test */
    public function it_sends_notification_to_single_user()
    {
        $user = User::factory()->create();

        $this->adapter->sendToUser($user->id, 'Test Title', 'Test Message', 'info');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'content' => 'Test Title: Test Message',
            'type' => 'info',
        ]);
    }

    /** @test */
    public function it_sends_notification_to_all_staff()
    {
        $staff1 = User::factory()->create(['role_id' => 5]);
        $staff2 = User::factory()->create(['role_id' => 5]);
        $nonStaff = User::factory()->create(['role_id' => 2]);

        $this->adapter->sendToStaff('Staff Alert');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $staff1->id,
            'content' => 'Staff Alert',
            'type' => 'ticket',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $staff2->id,
            'content' => 'Staff Alert',
            'type' => 'ticket',
        ]);

        // تأكد أن غير الموظفين ما وصلهم إشعار
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $nonStaff->id,
            'content' => 'Staff Alert',
        ]);
    }

    /** @test */
    public function it_excludes_specific_user_when_sending_to_staff()
    {
        $staff1 = User::factory()->create(['role_id' => 5]);
        $staff2 = User::factory()->create(['role_id' => 5]);

        $this->adapter->sendToStaff('Exclude Alert', $staff1->id);

        // تأكد أن staff2 وصله إشعار
        $this->assertDatabaseHas('notifications', [
            'user_id' => $staff2->id,
            'content' => 'Exclude Alert',
            'type' => 'ticket',
        ]);

        // تأكد أن staff1 ما وصله إشعار
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $staff1->id,
            'content' => 'Exclude Alert',
        ]);
    }
}