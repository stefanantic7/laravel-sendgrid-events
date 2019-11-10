<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use LaravelSendgridEvents\Events\SendgridEventCreated;
use LaravelSendgridEvents\Models\SendgridEvent;

class RequestTest extends TestCase
{
    public function testSuccessfulWebhook()
    {
        Event::fake([SendgridEventCreated::class]);

        // Laravels testing suite does not support posting a json string, so we have to load our JSON payload, decode it
        // and then fire it off...
        $payload = collect(json_decode(file_get_contents(__DIR__ . '/stubs/request_payload.json'), true));

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            config('sendgridevents.webhook_url'),
            $payload->toArray()
        );

        $uniquePayload = $payload->unique('sg_event_id');

        Event::assertDispatched(SendgridEventCreated::class, $uniquePayload->count());
        foreach ($uniquePayload as $event) {
            Event::assertDispatched(SendgridEventCreated::class, function (SendgridEventCreated $sendgridEventCreated) use ($event) {
                return $event['sg_event_id'] == $sendgridEventCreated->getSendgridEvent()->sg_event_id;
            });

            $this->assertDatabaseHas(config('sendgridevents.events_table_name'), [
                "email" => $event['email'],
                "timestamp" => Carbon::createFromTimestamp($event['timestamp']),
//                "smtp_id" => $event['smtp-id'],
                "event" => $event['event'],
                "categories" => json_encode([$event['category']]),
                "sg_event_id" => $event['sg_event_id'],
                "sg_message_id" => $event['sg_message_id']
            ]);
        }

        $result->assertStatus(200);
    }

    /**
     * A duplicate webhook should not create the same record twice
     */
    public function testDuplicateWebhook()
    {
        Event::fake([SendgridEventCreated::class]);

        $payload = [
            [
                "email" => "example@test.com",
                "timestamp" => 1554728844,
                "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
                "event" => "processed",
                "category" => "cat facts",
                "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
                "sg_message_id" => "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0"
            ],
            [
                "email" => "example@test.com",
                "timestamp" => 1554728844,
                "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
                "event" => "processed",
                "category" => "cat facts",
                "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
                "sg_message_id" => "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0"
            ]
        ];

        $preWebhookCount = SendgridEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            config('sendgridevents.webhook_url'),
            $payload
        );

        Event::assertDispatched(SendgridEventCreated::class, 1);
        Event::assertDispatched(SendgridEventCreated::class, function (SendgridEventCreated $sendgridEventCreated) use ($payload) {
            return $payload[0]['sg_event_id'] == $sendgridEventCreated->getSendgridEvent()->sg_event_id;
        });

        $this->assertDatabaseHas(config('sendgridevents.events_table_name'), [
            "email" => $payload[0]['email'],
            "timestamp" => Carbon::createFromTimestamp($payload[0]['timestamp']),
//                "smtp_id" => $event['smtp-id'],
            "event" => $payload[0]['event'],
            "categories" => json_encode([$payload[0]['category']]),
            "sg_event_id" => $payload[0]['sg_event_id'],
            "sg_message_id" => $payload[0]['sg_message_id']
        ]);

        $result->assertStatus(200);

        $postWebhookCount = SendgridEvent::count();
        $this->assertEquals(
            $preWebhookCount + 1,
            $postWebhookCount,
            'Only one new record should be created'
        );
    }

    public function testPayloadWithoutCategoryShouldBeSuccessful()
    {
        Event::fake([SendgridEventCreated::class]);

        $payload = [[
            "email" => "example@test.com",
            "timestamp" => 1554728844,
            "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
            "event" => "processed",
            "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
            "sg_message_id" => "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0"
        ]];

        $preWebhookCount = SendgridEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            config('sendgridevents.webhook_url'),
            $payload
        );

        Event::assertDispatched(SendgridEventCreated::class, 1);
        Event::assertDispatched(SendgridEventCreated::class, function (SendgridEventCreated $sendgridEventCreated) use ($payload) {
            return $payload[0]['sg_event_id'] == $sendgridEventCreated->getSendgridEvent()->sg_event_id;
        });

        $this->assertDatabaseHas(config('sendgridevents.events_table_name'), [
            "email" => $payload[0]['email'],
            "timestamp" => Carbon::createFromTimestamp($payload[0]['timestamp']),
//                "smtp_id" => $event['smtp-id'],
            "event" => $payload[0]['event'],
            "sg_event_id" => $payload[0]['sg_event_id'],
            "sg_message_id" => $payload[0]['sg_message_id']
        ]);

        $result->assertStatus(200);

        $postWebhookCount = SendgridEvent::count();

        $this->assertEquals($preWebhookCount + 1, $postWebhookCount);
    }

    public function testPayloadWithArrayCategoryShouldBeSuccessful()
    {
        Event::fake([SendgridEventCreated::class]);

        $messageId = "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.2";
        $category = "bird facts";
        $payload = [[
            "email" => "example@test.com",
            "timestamp" => 1554728844,
            "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
            "event" => "processed",
            "category" => [$category],
            "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
            "sg_message_id" => $messageId,
        ]];

        $preWebhookCount = SendgridEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            config('sendgridevents.webhook_url'),
            $payload
        );

        Event::assertDispatched(SendgridEventCreated::class, 1);
        Event::assertDispatched(SendgridEventCreated::class, function (SendgridEventCreated $sendgridEventCreated) use ($payload) {
            return $payload[0]['sg_event_id'] == $sendgridEventCreated->getSendgridEvent()->sg_event_id;
        });

        $this->assertDatabaseHas(config('sendgridevents.events_table_name'), [
            "email" => $payload[0]['email'],
            "timestamp" => Carbon::createFromTimestamp($payload[0]['timestamp']),
//                "smtp_id" => $event['smtp-id'],
            "categories" => json_encode($payload[0]['category']),
            "event" => $payload[0]['event'],
            "sg_event_id" => $payload[0]['sg_event_id'],
            "sg_message_id" => $payload[0]['sg_message_id']
        ]);

        $result->assertStatus(200);

        $postWebhookCount = SendgridEvent::count();

        $this->assertEquals($preWebhookCount + 1, $postWebhookCount);

        $newEvent = SendgridEvent::where('sg_message_id', $messageId)->first();
        $this->assertCount(1, $newEvent->categories, "Should be one item in categories array");
        $this->assertEquals($category, $newEvent->categories[0], 'Category should be saved in $categories');
    }

    public function testPayloadWithUnknownEventShouldBeRejected()
    {
        $payload = [[
            "email" => "example@test.com",
            "timestamp" => 1554728844,
            "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
            "event" => "wrongwrong",
            "category" => "cat facts",
            "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
            "sg_message_id" => "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0"
        ]];

        $preWebhookCount = SendgridEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            config('sendgridevents.webhook_url'),
            $payload
        );
        $result->assertStatus(422);
        $result->assertSee('The selected 0.event is invalid.');

        $postWebhookCount = SendgridEvent::count();

        $this->assertEquals($preWebhookCount, $postWebhookCount, "No new database rows should be created");
    }


    public function testNonArrayRejected()
    {
        $payload = ['wrong'];

        $preWebhookCount = SendgridEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            config('sendgridevents.webhook_url'),
            $payload
        );
        $result->assertStatus(422);
        $result->assertSee('The 0.email field is required.');

        $postWebhookCount = SendgridEvent::count();

        $this->assertEquals($preWebhookCount, $postWebhookCount, "No new database rows should be created");
    }

    public function testPayloadWithoutEmailShouldBeRejected()
    {
        $payload = [[
            "timestamp" => 1554728844,
            "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
            "event" => "processed",
            "category" => "cat facts",
            "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
            "sg_message_id" => "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0"
        ]];

        $preWebhookCount = SendgridEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            config('sendgridevents.webhook_url'),
            $payload
        );
        $result->assertStatus(422);
        $result->assertSee('The 0.email field is required.');

        $postWebhookCount = SendgridEvent::count();

        $this->assertEquals($preWebhookCount, $postWebhookCount, "No new database rows should be created");
    }
}
