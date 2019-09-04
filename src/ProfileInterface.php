<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log;

use yii\log;

/**
 * Interface ProfileInterface
 * @package Horat1us\Yii\Queue\Log
 *
 * @see \Yii::beginProfile()
 * @see \Yii::endProfile()
 */
interface ProfileInterface
{
    public const TYPE_START = 'start';
    public const TYPE_END = 'end';

    public function getToken(): string;

    public function getType(): string;
}
