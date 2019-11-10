<?php

namespace Antiques\LaravelSendgridEvents\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Antiques\LaravelSendgridEvents\Enums\EventEnum;
use Antiques\LaravelSendgridEvents\Events\SendgridEventCreated;
use Antiques\LaravelSendgridEvents\Models\SendgridEvent;
use Antiques\LaravelSendgridEvents\Repositories\SendgridEventRepositoryInterface;
use Psr\Log\LogLevel;

/**
 * Class WebhookController
 * Ingresses any Sendgrid webhooks
 *
 * @package LaravelSendgridEvents\Http\Controllers
 */
class WebhookController extends Controller
{
    use ValidatesRequests;

    /** @var SendgridEventRepositoryInterface */
    private $sendgridEventRepository;

    /**
     * WebhookController constructor.
     *
     * @param SendgridEvent $sendgridEvent
     * @param SendgridEventRepositoryInterface $sendgridEventRepository
     */
    public function __construct(SendgridEventRepositoryInterface $sendgridEventRepository)
    {
        $this->sendgridEventRepository = $sendgridEventRepository;
    }

    /**
     * @param Request $request
     *
     * @throws ValidationException
     * @throws \ReflectionException
     */
    public function post(Request $request)
    {
        $payload = $request->input();
        $validator = Validator::make(
            $payload,
            [
                '*.email' => 'required|email',
                '*.timestamp' => 'required|integer',
                '*.event' => 'required|in:' . implode(',', EventEnum::getAll()),
                '*.sg_event_id' => 'required|string',
                '*.sg_message_id' => 'required|string',
                '*.category' => function ($attribute, $value, $fail) {
                    if (!is_null($value) && !in_array(gettype($value), ['string', 'array'])) {
                        $fail($attribute.' must be a string or array.');
                    }
                },
                '*.category.*' => 'string',
            ]
        );
        if ($validator->fails()) {
            $this->logMalformedPayload($payload, $validator->errors()->all());
            throw new ValidationException($validator);
        }

        foreach ($payload as $event) {
            $this->processEvent($event);
        }
    }

    /**
     * Processes an individual event
     *
     * @param $event
     */
    private function processEvent(array $event): void
    {
        if ($this->sendgridEventRepository->exists($event['sg_event_id'])) {
            $this->logDuplicateEvent($event);
            return;
        }

        $sendgridEvent = $this->sendgridEventRepository->create($event);

        event(new SendgridEventCreated($sendgridEvent));
    }

    /**
     * Logs a message that we have received a malformed webhook
     * If the webhook was sent by Sendgrid then this may indicate Sendgrid has changed their payload structure and
     * therefore this library will need to be updated.
     *
     * Note: there is no way of validating that this webhook was actually sent by Sendgrid, so the malformation could
     * be the result of a malicious third party.
     *
     * @param array $event
     */
    private function logMalformedPayload($payload, array $validationErrors)
    {
        if (config('sendgridevents.log_malformed_payload')) {
            Log::log(
                config('sendgridevents.log_malformed_payload_level'),
                'Malformed Sendgrid webhook received',
                [
                    'payload' => $payload,
                    'validation_errors' => $validationErrors,
                ]
            );
        }
    }

    /**
     * Logs a message that we have received a duplicate webhook for an event
     *
     * @param array $event
     */
    private function logDuplicateEvent(array $event)
    {
        if (config('sendgridevents.log_duplicate_events')) {
            Log::log(
                config('sendgridevents.log_duplicate_events_level'),
                'Duplicate Sendgrid Webhook received',
                $event
            );
        }
    }
}
