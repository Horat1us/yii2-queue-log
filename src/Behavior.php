<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

use yii\queue;
use yii\base;

/**
 * Class Behavior
 * @package Horat1us\Yii\Queue\Log
 */
class Behavior extends base\Behavior
{
    public $autoFlush = true;

    public function events()
    {
        return [
            queue\Queue::EVENT_AFTER_PUSH => 'process',
            queue\Queue::EVENT_BEFORE_EXEC => 'process',
            queue\Queue::EVENT_AFTER_EXEC => 'process',
            queue\Queue::EVENT_AFTER_ERROR => 'process',
            queue\cli\Queue::EVENT_WORKER_START => 'process',
            queue\cli\Queue::EVENT_WORKER_STOP => 'process',
        ];
    }

    public function process(base\Event $event)
    {
        try {
            $message = $this->createMessage($event);
            \Yii::info($message, get_class($message));
        } catch (Error $error) {
            \Yii::error($error, get_class($error));
            $message = $error;
        }

        if ($message instanceof ProfileInterface) {
            switch ($message->getType()) {
                case ProfileInterface::TYPE_START:
                    \Yii::beginProfile($message->getToken(), get_class($message));
                    break;
                case ProfileInterface::TYPE_END:
                    \Yii::endProfile($message->getToken(), get_class($message));
                    if ($this->autoFlush) {
                        \Yii::getLogger()->flush(true);
                    }
                    break;
            }
        }
    }

    /**
     * @param base\Event $event
     * @return MessageInterface
     * @throws Error
     */
    protected function createMessage(base\Event $event): MessageInterface
    {
        if ($event instanceof queue\cli\WorkerEvent) {
            return WorkerMessage::createFromEvent($event);
        }
        if ($event instanceof queue\ExecEvent) {
            return ExecMessage::createFromEvent($event);
        }
        if ($event instanceof queue\PushEvent && $event->name === queue\Queue::EVENT_AFTER_PUSH) {
            return JobMessage::createFromEvent($event);
        }
        throw new \DomainException("Unsupported event: " . print_r($event));
    }
}
