# Yii2 Queue Log Behavior (Advanced)
[![Test & Lint](https://github.com/Horat1us/yii2-queue-log/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/Horat1us/yii2-queue-log/actions/workflows/php.yml)

Purpose of this package to create replacement for Yii2 built-in
[LogBehavior](https://github.com/yiisoft/yii2-queue/blob/2.3.0/src/LogBehavior.php).

Why replacement? Because built-in LogBehavior write logs in string format:
it cannot be easy parsed and processed.

This logger push same messages, but use classes:
- [Error](./src/Error.php) - queue job errors. Original exception will be stored
as \Previous (instead of string, compared to built-in LogBehavior).
- [MessageInterface](./src/MessageInterface.php) - another events. 

Compatibility: tested on PHP 8.4, PHP 8.5

## Installation
```bash
composer require horat1us/yii2-queue-log
```

## Usage
```php
<?php

use Horat1us\Yii\Queue;

return [
    'components' => [
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'as log' => Queue\Log\Behavior::class,
        ],
    ],
];
```

## Author
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)

## License
[MIT](./LICENSE)
