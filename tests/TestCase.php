<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use LaravelSendgridEvents\Events\SendgridEventCreated;
use LaravelSendgridEvents\Repositories\SendgridEventRepository;
use LaravelSendgridEvents\Repositories\SendgridEventRepositoryInterface;
use LaravelSendgridEvents\ServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseTransactions;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testing']);
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
