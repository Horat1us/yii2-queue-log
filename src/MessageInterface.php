<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

/**
 * Interface MessageInterface
 * @package Horat1us\Yii\Queue\Log
 */
interface MessageInterface extends \JsonSerializable
{
    public function __toString(): string;

    public function jsonSerialize(): array;
}
