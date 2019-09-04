<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

use yii\queue;

/**
 * Class JobMessage
 * @package Horat1us\Yii\Queue\Log
 */
class JobMessage implements MessageInterface
{
    use JobTrait;

    public function __toString(): string
    {
        return ":name (ID :id) is pushed.";
    }

    public static function createFromEvent(queue\JobEvent $event)
    {
        return new static(compact('event'));
    }

    public static function createFromValues(string $id, string $name)
    {
        return new static(compact('id', 'name'));
    }
}
