<?php

namespace Tests\Feature\Policies;

use App\Models\SupportedTicket;
use App\Models\User;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TicketPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TicketPolicy();
    }

    /** @test */
    public function customer_can_view_own_ticket()
    {
        $customer = User::factory()->create(['role_id' => 2]);
        $ticket = SupportedTicket::factory()->create(['customer_id' => $customer->id]);

        $this->assertTrue($this->policy->view($customer, $ticket));
    }

    /** @test */
    public function staff_can_view_any_ticket()
    {
        $staff = User::factory()->create(['role_id' => 5]);
        $ticket = SupportedTicket::factory()->create();

        $this->assertTrue($this->policy->view($staff, $ticket));
    }

    /** @test */
    // public function other_users_cannot_view_ticket()
    // {
    //     $user = User::factory()->create(['role_id' => 3]);
    //     $ticket = SupportedTicket::factory()->create(['customer_id' => 99]);

    //     $this->assertFalse($this->policy->view($user, $ticket));
    // }

    /** @test */
    public function customer_can_reply_to_own_ticket()
    {
        $customer = User::factory()->create(['role_id' => 2]);
        $ticket = SupportedTicket::factory()->create(['customer_id' => $customer->id]);

        $this->assertTrue($this->policy->reply($customer, $ticket));
    }

    /** @test */
    public function staff_can_reply_to_any_ticket()
    {
        $staff = User::factory()->create(['role_id' => 5]);
        $ticket = SupportedTicket::factory()->create();

        $this->assertTrue($this->policy->reply($staff, $ticket));
    }

    /** @test */
    // public function other_users_cannot_reply_to_ticket()
    // {
    //     $user = User::factory()->create(['role_id' => 3]);
    //     $ticket = SupportedTicket::factory()->create(['customer_id' => 99]);

    //     $this->assertFalse($this->policy->reply($user, $ticket));
    // }

    /** @test */
    public function only_staff_can_update_status()
    {
        $staff = User::factory()->create(['role_id' => 5]);
        $user = User::factory()->create(['role_id' => 2]);

        $this->assertTrue($this->policy->updateStatus($staff));
        $this->assertFalse($this->policy->updateStatus($user));
    }

    /** @test */
    public function only_staff_can_manage_tickets()
    {
        $staff = User::factory()->create(['role_id' => 5]);
        $user = User::factory()->create(['role_id' => 2]);

        $this->assertTrue($this->policy->manage($staff));
        $this->assertFalse($this->policy->manage($user));
    }
}