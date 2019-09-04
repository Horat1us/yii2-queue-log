<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

use yii\queue\ExecEvent;

/**
 * Trait ExecTrait
 * @package Horat1us\Yii\Queue\Log
 */
trait ExecTrait
{
    use JobTrait {
        getToken as private getJobToken;
        jsonSerialize as private jsonSerializeToken;
        __construct as private constructToken;
    }

    /** @var int */
    public $attempt;

    /** @var string|null */
    public $pid;

    protected function __construct(array $config)
    {
        $this->constructToken($config);
        if (array_key_exists('event', $config)) {
            $event = $config['event'];
            /** @var ExecEvent $event */
            $this->attempt = $event->attempt;
            $this->pid = $event->sender->workerPid;
        } else {
            $this->attempt = $config['attempt'];
            $this->pid = $config['pid'] ?? null;
        }
    }

    public function jsonSerialize(): array
    {
        $data = [
            'attempt' => $this->attempt,
        ];
        if (!is_null($this->pid)) {
            $data['pid'] = $this->pid;
        }
        return $this->jsonSerializeToken() + $data;
    }

    public function getToken(): string
    {
        $token = $this->getJobToken();
        $extra = "attempt: {$this->attempt}";
        if (!is_null($this->pid)) {
            $extra .= ", PID: {$this->pid}";
        }
        return "{$token} ({$extra})";
    }
}
