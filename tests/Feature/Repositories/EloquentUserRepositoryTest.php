<?php

namespace Tests\Feature\Repositories;

use App\Repositories\EloquentUserRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentUserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentUserRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new EloquentUserRepository();
    }

    /** @test */
    public function it_creates_user()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ];

        $user = $this->repo->create($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    /** @test */
    public function it_finds_user_by_email_or_phone()
    {
        $user = User::factory()->create(['email' => 'jane@example.com', 'phone' => '987654321']);

        $foundByEmail = $this->repo->findByEmailOrPhone('jane@example.com');
        $foundByPhone = $this->repo->findByEmailOrPhone('987654321');

        $this->assertEquals($user->id, $foundByEmail->id);
        $this->assertEquals($user->id, $foundByPhone->id);
    }

    /** @test */
    public function it_finds_user_by_id()
    {
        $user = User::factory()->create();
        $found = $this->repo->findById($user->id);

        $this->assertEquals($user->id, $found->id);
    }

    /** @test */
    public function it_updates_user()
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        $updated = $this->repo->update($user, ['name' => 'New Name']);

        $this->assertTrue($updated);
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    /** @test */
    public function it_creates_staff_with_default_password()
    {
        $data = [
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'phone' => '111222333',
            'role_id' => 2,
        ];

        $staff = $this->repo->createStaff($data);

        $this->assertInstanceOf(User::class, $staff);
        $this->assertDatabaseHas('users', ['email' => 'staff@example.com']);
        $this->assertTrue(password_verify('123123123', $staff->password));
    }

    /** @test */
    public function it_gets_all_staff()
    {
        // عميل
        User::factory()->create(['role_id' => 3])->role()->associate(['name' => 'Customer']);
        // موظف
        $staff = User::factory()->create(['role_id' => 2]);

        $result = $this->repo->allStaff();

        $this->assertTrue($result->contains(fn($u) => $u->id === $staff->id));
    }

    /** @test */
    public function it_updates_user_role()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $updatedUser = $this->repo->updateRole($user, 2);

        $this->assertEquals(2, $updatedUser->role_id);
    }

    /** @test */
    public function it_deletes_user()
    {
        $user = User::factory()->create();
        $deleted = $this->repo->delete($user);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}