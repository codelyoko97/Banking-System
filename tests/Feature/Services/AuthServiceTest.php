<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AuthService;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendOtpMailJob;

class AuthServiceTest extends TestCase
{
    protected $repo;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake(); // منع تنفيذ الـ Job فعليًا
        Log::spy();    // مراقبة الـ Log

        $this->repo = $this->createMock(UserRepositoryInterface::class);
        $this->service = new AuthService($this->repo);
    }

    /** ✅ اختبار تسجيل مواطن جديد */
    public function test_register_citizen_creates_user_and_sends_otp()
    {
        $data = ['email' => 'feras@test.com', 'password' => 'secret'];

        $user = new User(['id' => 1, 'email' => $data['email']]);
        $this->repo->method('create')->willReturn($user);
        $this->repo->method('update')->willReturn(true);

        $result = $this->service->registerCitizen($data);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('feras@test.com', $result->email);

        Queue::assertPushed(SendOtpMailJob::class);
    }

    /** ✅ اختبار التحقق من OTP صحيح */
    public function test_verify_otp_success()
    {
        $user = new User([
            'id' => 1,
            'otp_code' => '123456',
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        $this->repo->method('update')->willReturn(true);

        $result = $this->service->verifyOtp($user, '123456');

        $this->assertTrue($result);
    }

    /** ✅ اختبار التحقق من OTP خاطئ */
    public function test_verify_otp_failure()
    {
        $user = new User([
            'id' => 1,
            'otp_code' => '123456',
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        $result = $this->service->verifyOtp($user, '999999');

        $this->assertFalse($result);
    }

    /** ✅ اختبار تسجيل دخول ناجح */
    public function test_attempt_login_success()
    {
        $password = Hash::make('secret');
        $user = new User([
            'id' => 1,
            'email' => 'feras@test.com',
            'password' => $password,
            'failed_attempts' => 0,
        ]);

        $this->repo->method('findByEmailOrPhone')->willReturn($user);
        $this->repo->method('update')->willReturn(true);

        $result = $this->service->attemptLogin('feras@test.com', 'secret');

        $this->assertTrue($result['success']);
        $this->assertEquals($user, $result['user']);
    }

    /** ✅ اختبار تسجيل دخول فاشل */
    public function test_attempt_login_failure()
    {
        $password = Hash::make('secret');
        $user = new User([
            'id' => 1,
            'email' => 'feras@test.com',
            'password' => $password,
            'failed_attempts' => 4,
        ]);

        $this->repo->method('findByEmailOrPhone')->willReturn($user);
        $this->repo->method('update')->willReturn(true);

        $result = $this->service->attemptLogin('feras@test.com', 'wrongpass');

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid credentials', $result['message']);
    }
}