<?php

namespace Tests\Feature\Services;

use App\Services\StaffService;
use App\DTO\Dashboard\StaffDTO as DashboardStaffDTO;
use App\Models\Account;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class StaffServiceTest extends TestCase
{
    use RefreshDatabase;

    private $repoMock;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repoMock = Mockery::mock(UserRepositoryInterface::class);
        $this->service = new StaffService($this->repoMock);
    }

    /** @test */
    public function it_creates_staff_successfully()
    {
        $dto = new DashboardStaffDTO(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret',
            phone: '123456789',
            role_id: 2
        );

        $createdUser = new User([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'role_id' => 2,
            'is_verified' => true,
        ]);

        $this->repoMock
            ->shouldReceive('createStaff')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['name'] === 'John Doe'
                    && $data['email'] === 'john@example.com'
                    && Hash::check('secret', $data['password']);
            }))
            ->andReturn($createdUser);

        $user = $this->service->createStaff($dto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals(2, $user->role_id);
    }

    /** @test */
    public function it_lists_all_staff()
    {
        $users = collect([new User(['id' => 1, 'name' => 'Staff'])]);

        $this->repoMock
            ->shouldReceive('allStaff')
            ->once()
            ->andReturn($users);

        $result = $this->service->listStaff();

        $this->assertCount(1, $result);
        $this->assertEquals('Staff', $result[0]->name);
    }

    /** @test */
    public function it_updates_role()
    {
        $user = User::factory()->create(['role_id' => 2]);

        $updatedUser = new User([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => 3,
            'phone' => $user->phone,
            'is_verified' => true,
        ]);

        $this->repoMock
            ->shouldReceive('updateRole')
            ->once()
            ->with(Mockery::type(User::class), 3)
            ->andReturn($updatedUser);

        $result = $this->service->updateRole($user->id, 3);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(3, $result->role_id);
        $this->assertEquals($user->id, $result->id);
    }

    /** @test */
    // public function it_gets_all_employees()
    // {
    //     // مستخدمين ليسوا موظفين
    //     User::factory()->create(['role_id' => 1, 'name' => 'Manager']);
    //     User::factory()->create(['role_id' => 2, 'name' => 'Supervisor']);

    //     // الموظفون (role_id = 4)
    //     $john = User::factory()->create(['role_id' => 4, 'name' => 'John Doe', 'email' => 'john@example.com']);
    //     $jane = User::factory()->create(['role_id' => 4, 'name' => 'Jane Smith', 'email' => 'jane@example.com']);

    //     $employees = $this->service->getAllEmployees();

    //     // فلترة النتيجة للتأكد من الموظفين فقط
    //     $onlyEmployees = $employees->filter(fn($u) => (int) $u->role_id === 1);

    //     $this->assertCount(2, $onlyEmployees);

    //     $ids = $onlyEmployees->pluck('id')->all();
    //     $this->assertContains($john->id, $ids);
    //     $this->assertContains($jane->id, $ids);

    //     $names = $onlyEmployees->pluck('name')->all();
    //     $this->assertContains('John Doe', $names);
    //     $this->assertContains('Jane Smith', $names);
    // }

    /** @test */
    public function it_deletes_staff()
    {
        $user = User::factory()->create();

        $this->repoMock
            ->shouldReceive('delete')
            ->once()
            ->with(Mockery::type(User::class))
            ->andReturn(true);

        $result = $this->service->deleteStaff($user->id);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_gets_account_user()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['number' => 'ACC123', 'customer_id' => $user->id]);

        $result = $this->service->getAccountUser('ACC123');

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }
}