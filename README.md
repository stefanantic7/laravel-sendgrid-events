# Laravel Sendgrid Events

This package enables your Laravel application to receive event webhooks from Sendgrid, and optionaly will store those 
events in your database. The package also fires Laravel events so you can hook in to the webhooks and take your own 
actions.


## Installation

### Getting the package

```shell script
composer require antiques/laravel-sendgrid-events
```


### Copy the config file (optional)

This library works without any local configuration, however you may want to use the config file in order to tweak the logs you receive. (eg. to receive logs when you receive duplicate events or to enable event storing in the database). 

Call the command below to copy the package config files:
```php
php artisan vendor:publish --provider="Antiques\LaravelSendgridEvents\ServiceProvider" --tag=config
```

### Run the migrations (optional)

Into config file `sendgridevents.php`, the option that handle storing events `store_events_into_database` is disabled by default and migrations will not run automatically.
If `store_events_into_database` is enabled, migrations will be registered and you should run:

```php
php artisan migrate
```

After that, all events sent by Sendgrid will be automatically stored in the database.

### Copy the migrations files (optional)

You are able to configure migrations on your way by running:

```php
php artisan vendor:publish --provider="Antiques\LaravelSendgridEvents\ServiceProvider" --tag=migrations
```

### Tell Sendgrid to use your new event webhook URL

Head over to https://app.sendgrid.com/settings/mail_settings and click on the 'Event Notification' section.

Your HTTP Post URL is: `https://yourwebsite.com/sendgrid/webhook`


## Using the library

### Listen to events

Each time new event is registered the package will trigger `\Antiques\LaravelSendgridEvents\Events\SendgridEventCreated`.
Based on that, you could define listeners in your EventServiceProvider and handle further logic.

```php
public function handle(SendgridEventCreated $sendgridEventCreated)
{
    $eventType = $sendgridEventCreated->getEventType(); //click, open...

    /**
     * ...
     */
    
    $sendgridEvent = $sendgridEventCreated->getSendgridEvent();
    $sendgridEvent->email;
    $sendgridEvent->timestamp;
    
    /**
     * ...
     */
}
```


### Querying records

This library uses a standard Laravel Eloquent model, so you can therefore query it as you would any other model.

```php
// Include the class
use \LaravelSendgridEvents\Models\SendgridEvent;

/**
* ...
*/

// Get all records:
SendgridEvent::all();

// Get all by message ID:
$sendgridMessageId = 'abc123';
SendgridEvent::where('sg_message_id', $sendgridMessageId)->all();

// Get all by event ID:
$sendgridEventId = 'xyz987';
SendgridEvent::where('sg_event_id', $sendgridEventId)->all();

// Get all by event type
SendgridEvent::where('event', LaravelSendgridEvents\Enums::PROCESSED)->all();
SendgridEvent::where('event', LaravelSendgridEvents\Enums::DEFERRED)->all();
SendgridEvent::where('event', LaravelSendgridEvents\Enums::DELIVERED)->all();
SendgridEvent::where('event', LaravelSendgridEvents\Enums::OPEN)->all();
SendgridEvent::where('event', LaravelSendgridEvents\Enums::CLICK)->all();
SendgridEvent::where('event', LaravelSendgridEvents\Enums::BOUNCE)->all();
SendgridEvent::where('event', LaravelSendgridEvents\Enums::DROPPED)->all();
SendgridEvent::where('event', LaravelSendgridEvents\Enums::SPAMREPORT)->all();
SendgridEvent::where('event', LaravelSendgridEvents\Enums::UNSUBSCRIBE)->all();
SendgridEvent::where('event', LaravelSendgridEvents\Enums::GROUP_UNSUBSCRIBE)->all();
SendgridEvent::where('event', LaravelSendgridEvents\Enums::GROUP_RESUBSCRIBE)->all();

// Count all bounces
SendgridEvent::where('event', LaravelSendgridEvents\Enums::BOUNCE)->count();
```

### Interacting with a record

Accessing data included with all event types:

```php
// Get a record
$event = SendgridEvent::first();

// Included with all event types
$event->timestamp;
$event->email;
$event->event;
$event->categories;
$event->sg_event_id;
$event->sg_message_id;
$event->payload; // Array of full payload sent by Sendgrid
```

Some data is only included with specific events. You can find out what these attributes are here: https://sendgrid.com/docs/API_Reference/Event_Webhook/event.html#-Event-objects

We include this data under the payload array within a record. For example:
```php
// Get a record
$event = SendgridEvent::first();

// Access the reason attribute, included on 'dropped' and 'bounced' events.
$event->payload['reason'];
```
