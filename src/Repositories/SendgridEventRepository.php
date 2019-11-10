<?php


namespace Antiques\LaravelSendgridEvents\Repositories;


use Antiques\LaravelSendgridEvents\Models\SendgridEvent;

class SendgridEventRepository implements SendgridEventRepositoryInterface
{
    /** @var SendgridEvent */
    private $model;

    /**
     * SendgridEventRepository constructor.
     * @param SendgridEvent $sendgridEvent
     */
    public function __construct(SendgridEvent $sendgridEvent)
    {
        $this->model = $sendgridEvent;
    }

    /**
     * @inheritDoc
     */
    public function exists($sg_event_id): bool
    {
        return $this->model->newQuery()->where('sg_event_id', $sg_event_id)->exists();
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

        $newEvent->save();

        return $newEvent;
    }
}
