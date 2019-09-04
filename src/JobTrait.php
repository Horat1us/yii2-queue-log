<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

use yii\queue;

/**
 * Trait JobTrait
 * @package Horat1us\Yii\Queue\Log
 */
trait JobTrait
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    public function getToken(): string
    {
        return "[$this->id] {$this->name}";
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    protected function __construct(array $config)
    {
        if (array_key_exists('event', $config)) {
            $this->setJobEvent($config['event']);
        } else {
            ['id' => $this->id, 'name' => $this->name] = $config;
        }
    }

    protected function setJobEvent(queue\JobEvent $event)
    {
        $this->id = $event->id;
        $this->name = $event->job instanceof queue\JobInterface ? get_class($event->job) : 'unknown job';
    }
}
