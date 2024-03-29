<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

use yii\queue;

/**
 * Class Error
 * @package Horat1us\Yii\Queue\Log
 */
class Error extends \Exception implements ProfileInterface
{
    use ProfileTrait, ExecTrait {
        ProfileTrait::jsonSerialize insteadof ExecTrait;

        ProfileTrait::jsonSerialize as private jsonSerializeProfile;
        ExecTrait::jsonSerialize as private jsonSerializeExec;
        ExecTrait::__construct as private constructExec;
    }

    protected function __construct(array $config)
    {
        $this->type = ProfileInterface::TYPE_DONE;
        $this->constructExec($config);

        if (array_key_exists('previous', $config)) {
            $previous = $config['previous'];
        } else {
            /** @var queue\ExecEvent $event */
            $event = $config['event'];
            $previous = $event->error;
        }

        $message = "{$this->name} Error " . get_class($previous) . ": " . $previous->getMessage();
        parent::__construct($message, (int)$previous->getCode(), $previous);
    }

    public function __toString(): string
    {
        return parent::__toString() . PHP_EOL
            . 'Additional Information:' . PHP_EOL . print_r($this->jsonSerialize(), true);
    }

    public function jsonSerialize(): array
    {
        return array_merge($this->jsonSerializeProfile(), $this->jsonSerializeExec());
    }

    public static function createFromEvent(queue\ExecEvent $event): Error
    {
        if (!$event->error) {
            throw new \InvalidArgumentException(
                "Unable to create " . static::class . " from successful event " . print_r($event)
            );
        }
        return new static(compact('event'));
    }

    public static function createFromValues(array $config): Error
    {
        static $keys = ['id', 'name', 'attempt', 'previous',];
        if ($diff = array_diff_key($keys, $config)) {
            throw new \InvalidArgumentException(
                "Missing config keys: " . implode(", ", $diff)
            );
        }
        return new static($config);
    }
}
