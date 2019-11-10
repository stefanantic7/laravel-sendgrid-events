<?php

use Psr\Log\LogLevel;

return [
    /**
     * Mark as true if you would like a log when you receive a malformed Sendgrid webhook.
     *
     * If the webhook was sent by Sendgrid then this may indicate Sendgrid has changed their payload structure and
     * therefore this library will need to be updated.
     *
     * Note: there is no way of validating that this webhook was actually sent by Sendgrid, so the malformation could
     * be the result of a malicious third party.
     */
    'log_malformed_payload' => true,

    /**
     * If you are logging malformed payloads, what level would you like the log message.
     */
    'log_malformed_payload_level' => LogLevel::WARNING,

    /**
     * Mark as true if you would like a log when you receive a duplicate Sendgrid webhook for an event.
     *
     * According to the Sendgrid documentation receiving duplicate events occassionally is expected, and this library
     * will not save duplicates.
     */
    'log_duplicate_events' => true,

    /**
     * If you are logging duplicate events, what level would you like the log message.
     */
    'log_duplicate_events_level' => LogLevel::INFO,

    /**
     * Mark as true if you would like to register migrations for the package.
     * Also, if the parameter is true, all registered events will be stored into the table
     * created by the migrations.
     */
    'store_events_into_database' => false,

    /**
     * Set connection name of the database where events table will be created and used.
     * Value `null` will be considered as default connection.
     */
    'database_connection_for_events' => null,

    /**
     * Set name of table where events will be stored.
     */
    'events_table_name' => 'sendgrid_events',

    /**
     * Webhook URL on which Sendgrid post events.
     */
    'webhook_url' => 'sendgrid/webhook'
];
