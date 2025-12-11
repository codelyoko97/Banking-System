<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $authService;

    protected function disableFormRequestValidation()
    {
        $this->app->resolving(\Illuminate\Foundation\Http\FormRequest::class, function ($request) {
            $validator = \Illuminate\Support\Facades\Validator::make([], []);

            $reflect = new \ReflectionClass($request);
            if ($reflect->hasProperty('validator')) {
                $property = $reflect->getProperty('validator');
                $property->setAccessible(true);
                $property->setValue($request, $validator);
            }
        });
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->disableFormRequestValidation();

        $this->authService = Mockery::mock(AuthService::class);
        $this->app->instance(AuthService::class, $this->authService);
    }

    /** @test */
    public function it_registers_a_new_citizen()
    {
        $user = User::factory()->make(['id' => 1]);

        $this->authService->shouldReceive('registerCitizen')
            ->once()
            ->andReturn($user);

        $response = $this->postJson('/api/register', [
            'name' => 'Bshara',
            'email' => 'bshara@example.com',
            'phone' => '123456789',
            'password' => 'secret',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Registered. OTP sent', 'user_id' => 1]);
    }

    /** @test */
    public function it_verifies_otp_successfully()
    {
        $user = User::factory()->create();

        $this->authService->shouldReceive('verifyOtp')
            ->with(Mockery::on(fn ($u) => $u->id === $user->id), '123456')
            ->once()
            ->andReturn(true);

        $response = $this->postJson('/api/verify-otp', [
            'user_id' => $user->id,
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Verified']);
    }

    /** @test */
    public function it_fails_verifying_otp_if_user_not_found()
    {
        $response = $this->postJson('/api/verify-otp', [
            'user_id' => 999,
            'otp' => '123456',
        ]);

        $response->assertStatus(404)
                 ->assertJson(['message' => 'User not found']);
    }

    /** @test */
    public function it_fails_verifying_otp_if_invalid()
    {
        $user = User::factory()->create();

        $this->authService->shouldReceive('verifyOtp')
            ->with(Mockery::on(fn ($u) => $u->id === $user->id), 'wrong')
            ->once()
            ->andReturn(false);

        $response = $this->postJson('/api/verify-otp', [
            'user_id' => $user->id,
            'otp' => 'wrong',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Invalid or expired OTP']);
    }

    /** @test */
    public function it_resends_otp_successfully()
    {
        $user = User::factory()->create(['is_verified' => false]);

        $this->authService->shouldReceive('generateAndSendOtp')
            ->with(Mockery::on(fn ($u) => $u->id === $user->id))
            ->once();

        $response = $this->postJson('/api/resend-otp', [
            'user_id' => $user->id,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'OTP resent']);
    }

    /** @test */
    public function it_fails_resend_otp_if_user_not_found()
    {
        $response = $this->postJson('/api/resend-otp', [
            'user_id' => 999,
        ]);

        $response->assertStatus(404)
                 ->assertJson(['message' => 'User not found']);
    }

    /** @test */
    public function it_fails_resend_otp_if_already_verified()
    {
        $user = User::factory()->create(['is_verified' => true]);

        $response = $this->postJson('/api/resend-otp', [
            'user_id' => $user->id,
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Account already verified']);
    }

    /** @test */
    public function it_logs_in_successfully()
    {
        $user = User::factory()->create(['is_verified' => true]);

        $this->authService->shouldReceive('attemptLogin')
            ->with($user->email, 'secret')
            ->once()
            ->andReturn(['success' => true, 'user' => $user]);

        $response = $this->postJson('/api/login', [
            'identifier' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }

    /** @test */
    public function it_fails_login_if_invalid_credentials()
    {
        $this->authService->shouldReceive('attemptLogin')
            ->with('wrong@example.com', 'wrong')
            ->once()
            ->andReturn(['success' => false, 'message' => 'Invalid credentials']);

        $response = $this->postJson('/api/login', [
            'identifier' => 'wrong@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid credentials']);
    }

    /** @test */
    public function it_fails_login_if_not_verified()
    {
        $user = User::factory()->create(['is_verified' => false]);

        $this->authService->shouldReceive('attemptLogin')
            ->with($user->email, 'secret')
            ->once()
            ->andReturn(['success' => true, 'user' => $user]);

        $response = $this->postJson('/api/login', [
            'identifier' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Account not verified']);
    }

    /** @test */
}
