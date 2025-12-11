<?php

namespace Tests\Unit\Banking\Transactions\Approval;

use App\Banking\Transactions\Approval\ApprovalHandler;
use App\Banking\Transactions\Approval\SystemApproval;
use App\Banking\Transactions\Approval\TellerApproval;
use App\Banking\Transactions\Approval\ManagerApproval;
use PHPUnit\Framework\TestCase;

class ApprovalHandlerTest extends TestCase
{
    public function test_system_implements_interface()
    {
        $this->assertInstanceOf(ApprovalHandler::class, new SystemApproval());
    }

    public function test_teller_implements_interface()
    {
        $this->assertInstanceOf(ApprovalHandler::class, new TellerApproval());
    }

    public function test_manager_implements_interface()
    {
        $this->assertInstanceOf(ApprovalHandler::class, new ManagerApproval());
    }
}