<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log\Tests;

use yii\base;
use yii\queue;

class Job extends base\BaseObject implements queue\JobInterface
{
    public ?string $error = null;

    public function execute($queue)
    {
        if (!is_null($this->error)) {
            throw new \RuntimeException($this->error, 7);
        }
    }
}
