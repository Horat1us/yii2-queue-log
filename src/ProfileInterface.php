<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

/**
 * Interface ProfileInterface
 * @package Horat1us\Yii\Queue\Log
 *
 * @see \Yii::beginProfile()
 * @see \Yii::endProfile()
 */
interface ProfileInterface
{
    public const TYPE_BEGIN = 'begin';
    public const TYPE_DONE = 'done';

    public function getToken(): string;

    public function getType(): string;
}
