<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

use yii\queue;
use yii\queue\ExecEvent;

/**
 * Class ExecMessage
 * @package Horat1us\Yii\Queue\Log
 */
class ExecMessage implements MessageInterface, ProfileInterface
{
    public const ERROR_INVALID_EVENT_NAME = 11;

    use ProfileTrait;
    use ExecTrait {
        setExecEvent as private baseSetExecEvent;
    }

    /**
     * @param ExecEvent $event
     * @throws Error
     */
    protected function setExecEvent(ExecEvent $event): void
    {
        $this->baseSetExecEvent($event);
        switch ($event->name) {
            case queue\Queue::EVENT_BEFORE_EXEC:
                $this->setType(static::TYPE_END);
                break;
            case queue\Queue::EVENT_AFTER_EXEC:
                $this->setType(static::TYPE_START);
                break;
            case queue\Queue::EVENT_AFTER_ERROR:
                throw Error::createFromEvent($event);
            default:
                throw new \DomainException(
                    "Unsupported event name {$event->name}.",
                    static::ERROR_INVALID_EVENT_NAME
                );
        }
    }

    /**
     * @param queue\ExecEvent $event
     * @return ExecMessage
     * @throws Error
     */
    public static function createFromEvent(queue\ExecEvent $event): ExecMessage
    {
        return new static(compact('event'));
    }

    /**
     * @param array $config
     * @return ExecMessage
     * @throws Error
     */
    public static function createFromValues(array $config): ExecMessage
    {
        static $keys = ['id', 'name', 'attempt', 'type',];
        if ($diff = array_diff_key($keys, $config)) {
            throw new \InvalidArgumentException(
                "Missing config keys: " . implode(", ", $diff)
            );
        }
        return new static($config);
    }
}
