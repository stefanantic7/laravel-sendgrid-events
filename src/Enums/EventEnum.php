<?php

namespace Antiques\LaravelSendgridEvents\Enums;

/**
 * Class EventEnum
 *
 * @package LaravelSendgridEvents\Enums
 */
class EventEnum
{
    const PROCESSED = 'processed';
    const DEFERRED = 'deferred';
    const DELIVERED = 'delivered';
    const OPEN = 'open';
    const CLICK = 'click';
    const BOUNCE = 'bounce';
    const DROPPED = 'dropped';
    const SPAMREPORT = 'spamreport';
    const UNSUBSCRIBE = 'unsubscribe';
    const GROUP_UNSUBSCRIBE = 'group_unsubscribe';
    const GROUP_RESUBSCRIBE = 'group_resubscribe';

    const PROCESSED_INT = 1;
    const DEFERRED_INT = 2;
    const DELIVERED_INT = 3;
    const OPEN_INT = 4;
    const CLICK_INT = 5;
    const BOUNCE_INT = 6;
    const DROPPED_INT = 7;
    const SPAMREPORT_INT = 8;
    const UNSUBSCRIBE_INT = 9;
    const GROUP_UNSUBSCRIBE_INT = 10;
    const GROUP_RESUBSCRIBE_INT = 11;

    /**
     * @return string[]
     */
    public static function getAll(): array
    {
        try {
            $constants = (new \ReflectionClass(get_called_class()))->getConstants();
            $stringConstants = array_filter($constants, function ($key) use ($constants) {
                    return is_string($constants[$key]);
                },
                ARRAY_FILTER_USE_KEY
            );

            return $stringConstants;
        } catch (\ReflectionException $e) {
            return [];
        }
    }
}
