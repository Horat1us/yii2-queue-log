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

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
        ];
    }

    protected function setType(string $type): void
    {
        if ($type !== ProfileInterface::TYPE_BEGIN && $type !== ProfileInterface::TYPE_DONE) {
            throw new \InvalidArgumentException(
                "Invalid type given: " . print_r($type)
            );
        }
        $this->type = $type;
    }
}
