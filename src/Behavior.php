<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

use yii\queue;
use yii\base;
use yii\log;

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
        $logger = \Yii::getLogger();

        try {
            $message = $this->createMessage($event);
            $logger->log(
                $message,
                log\Logger::LEVEL_INFO,
                get_class($message)
            );
        } catch (Error $error) {
            $logger->log(
                $error,
                log\Logger::LEVEL_ERROR,
                get_class($error)
            );
            $message = $error;
        }

        if ($message instanceof ProfileInterface) {
            switch ($message->getType()) {
                case ProfileInterface::TYPE_BEGIN:
                    $logger->log(
                        $message->getToken(),
                        log\Logger::LEVEL_PROFILE_BEGIN,
                        get_class($message)
                    );
                    break;
                case ProfileInterface::TYPE_DONE:
                    $logger->log(
                        $message->getToken(),
                        log\Logger::LEVEL_PROFILE_END,
                        get_class($message)
                    );
                    if ($this->autoFlush) {
                        $logger->flush(true);
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
