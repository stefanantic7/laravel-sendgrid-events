<?php

namespace LaravelSendgridEvents\Enums;

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

    /**
     * @return string[]
     */
    public static function getAll(): array
    {
        try {
            return (new \ReflectionClass(get_called_class()))->getConstants();
        } catch (\ReflectionException $e) {
            return [];
        }
    }
}
