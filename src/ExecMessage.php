<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

use yii\queue;

/**
 * Class ExecMessage
 * @package Horat1us\Yii\Queue\Log
 */
class ExecMessage implements MessageInterface, ProfileInterface
{
    use ProfileTrait;
    use ExecTrait {
        __construct as private constructExec;
    }

    /**
     * @throws Error
     */
    protected function __construct(array $config)
    {
        $this->constructExec($config);

        if (array_key_exists('event', $config)) {
            /** @var queue\ExecEvent $event */
            $event = $config['event'];
            switch ($event->name) {
                case queue\Queue::EVENT_AFTER_EXEC:
                    $this->setType(static::TYPE_END);
                    break;
                case queue\Queue::EVENT_AFTER_PUSH:
                    $this->setType(static::TYPE_START);
                    break;
                case queue\Queue::EVENT_AFTER_ERROR:
                    throw Error::createFromEvent($event);
            }
        } else {
            $this->setType($config['type']);
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
