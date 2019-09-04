<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

use yii\queue\cli;

/**
 * Class WorkerMessage
 * @package Horat1us\Yii\Queue\Log
 */
class WorkerMessage implements MessageInterface, ProfileInterface
{
    use ProfileTrait;

    public const STATUS_UP = 'up';
    public const STATUS_DOWN = 'down';

    public const ERROR_INVALID_EVENT_NAME = 1;
    public const ERROR_MISSING_WORKER_PID = 2;

    /** @var int */
    protected $pid;

    public function __construct(int $pid, string $type)
    {
        $this->pid = $pid;
        $this->setType($type);
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function getToken(): string
    {
        return "Worker {$this->pid}";
    }

    public function getStatus(): string
    {
        static $statuses = [
            ProfileInterface::TYPE_BEGIN => WorkerMessage::STATUS_UP,
            ProfileInterface::TYPE_DONE => WorkerMessage::STATUS_DOWN,
        ];
        return $statuses[$this->type];
    }

    public function jsonSerialize(): array
    {
        return [
            'pid' => $this->pid,
            'status' => $this->getStatus(),
        ];
    }

    public static function createFromEvent(cli\WorkerEvent $event): WorkerMessage
    {
        switch ($event->name) {
            case cli\Queue::EVENT_WORKER_START:
                $type = static::TYPE_BEGIN;
                break;
            case cli\Queue::EVENT_WORKER_STOP:
                $type = static::TYPE_DONE;
                break;
            default:
                throw new \DomainException(
                    "Unsupported event name {$event->name}.",
                    static::ERROR_INVALID_EVENT_NAME
                );
        }

        if (is_null($pid = $event->sender->workerPid)) {
            throw new \DomainException(
                "Worker PID have to be defined.",
                static::ERROR_MISSING_WORKER_PID
            );
        }

        return new static($pid, $type);
    }

    public function __toString(): string
    {
        return "Worker :pid :down";
    }
}
