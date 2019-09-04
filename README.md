# Yii2 Queue Log Behavior (Advanced)

Purpose of this package to create replacement for Yii2 built-in
[LogBehavior](https://github.com/VEKsoftware/yii2-log-behavior/blob/master/behaviors/Log.php).

Why replacement? Because built-in LogBehavior write logs in string format:
it cannot be easy parsed and processed.

This logger push same messages, but use classes:
- [Error](./src/Error.php) - queue job errors. Original exception will be stored
as \Previous (instead of string, compared to built-in LogBehavior).
- [MessageInterface](./src/MessageInterface.php) - another events. 

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
