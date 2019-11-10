<?php


namespace LaravelSendgridEvents\Repositories;


use LaravelSendgridEvents\Models\SendgridEvent;

/**
 * Class SendgridEventRepositoryDisabled
 * Class will mock the database.
 * No queries will be applied.
 * @package LaravelSendgridEvents\Repositories
 */
class SendgridEventRepositoryDisabled implements SendgridEventRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function exists($sg_event_id): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function create($event): SendgridEvent
    {
        $newEvent = new SendgridEvent();
        $newEvent->timestamp = $event['timestamp'];
        $newEvent->email = $event['email'];
        $newEvent->event = $event['event'];
        $newEvent->sg_event_id = $event['sg_event_id'];
        $newEvent->sg_message_id = $event['sg_message_id'];
        $newEvent->payload = $event;

        if (!empty($event['category'])) {
            $category = $event['category'];
            if (gettype($category) === "string") {
                $newEvent->categories = [$category];
            } else {
                $newEvent->categories = $category;
            }
        }

        return $newEvent;
    }
}
