<?php

Route::group(['namespace' => 'LaravelSendgridEvents\Http\Controllers'], function () {
    Route::post(
        config('sendgridevents.webhook_url'),
        [
            'as' => 'sendgrid.webhook',
            'uses' => 'WebhookController@post'
        ]
    );
});