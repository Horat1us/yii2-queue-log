<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

/**
 * Trait ProfileTrait
 * @package Horat1us\Yii\Queue\Log
 * @see ProfileInterface
 */
trait ProfileTrait
{
    /** @var string */
    protected $type;

    public function getType(): string
    {
        return $this->type;
    }

    abstract public function getToken(): string;

    public function __toString(): string
    {
        $action = $this->type === ProfileInterface::TYPE_START ? "started" : "stopped";
        return "{$this->getToken()} is {$action}.";
    }

    protected function setType(string $type): void
    {
        if ($type !== ProfileInterface::TYPE_START && $type !== ProfileInterface::TYPE_END) {
            throw new \InvalidArgumentException(
                "Invalid type given: " . print_r($type)
            );
        }
        $this->type = $type;
    }
}
