<?php

namespace LaravelSendgridEvents\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use LaravelSendgridEvents\Events\SendgridEventCreated;

/**
 * Class SendgridEvent
 *
 * @package LaravelSendgridEvents\Models
 *
 * @property array|string[] $categories
 * @property Carbon $created_at
 * @property string $email
 * @property string $event
 * @property int $id
 * @property string $sg_event_id
 * @property string $sg_message_id
 * @property array $payload
 * @property Carbon $timestamp
 * @property Carbon $updated_at
 */
class SendgridEvent extends Model
{

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['timestamp'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'payload' => 'array',
        'categories' => 'array',
    ];

    /**
     * Get the current connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        return config('sendgridevents.database_connection_for_events');
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return config('sendgridevents.events_table_name');
    }
}
