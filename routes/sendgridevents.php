<?php

use Antiques\LaravelSendgridEvents\Http\Middleware\SendgridEventMiddleware;

Route::group([
    'namespace' => 'Antiques\LaravelSendgridEvents\Http\Controllers',
    'middleware' => SendgridEventMiddleware::class
], function () {
    Route::post(
        config('sendgridevents.webhook_url'),
        [
            'as' => 'sendgrid.webhook',
            'uses' => 'WebhookController@post'
        ]
    );
});
