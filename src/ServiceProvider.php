<?php

namespace LaravelSendgridEvents;

use LaravelSendgridEvents\Repositories\SendgridEventRepository;
use LaravelSendgridEvents\Repositories\SendgridEventRepositoryDisabled;
use LaravelSendgridEvents\Repositories\SendgridEventRepositoryInterface;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sendgridevents.php', 'sendgridevents');

        if(config('sendgridevents.store_events_into_database') || $this->app->runningUnitTests()) {
            $this->app->bind(SendgridEventRepositoryInterface::class, SendgridEventRepository::class);
        }
        else {
            $this->app->bind(SendgridEventRepositoryInterface::class, SendgridEventRepositoryDisabled::class);
        }

    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/sendgridevents.php' => config_path('sendgridevents.php')
        ]);
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations')
        ], 'migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/sendgridevents.php');

        if(config('sendgridevents.store_events_into_database') || $this->app->runningUnitTests()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }
}
