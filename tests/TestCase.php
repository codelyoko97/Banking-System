<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
  //
  protected function setUp(): void
  {
    parent::setUp();
    $this->artisan('migrate');
    // run seeders needed for tests
    $this->seed(\Database\Seeders\StatusAccountSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
  }
}
