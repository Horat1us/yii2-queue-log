<?php

declare(strict_types=1);

namespace Horat1us\Yii\Queue\Log\Tests;

use Horat1us\Yii\Queue\Log;
use PHPUnit\Framework\TestCase;
use yii\log\Logger;
use yii\queue\cli;

class BehaviorTest extends TestCase
{
    private const PID = 1234;

    private Log\Behavior $behavior;

    private cli\Queue $queue;

    private Logger $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new class extends Logger {
            public array $logged = [];

            public int $flushed = 0;

            public function log($message, $level, $category = 'application')
            {
                $this->logged[] = [$message, $level, $category];
            }

            public function flush($final = false)
            {
                $this->flushed++;
            }
        };
        \Yii::setLogger($this->logger);

        $this->queue = new class extends cli\Queue {
            public array $messages = [];

            protected function pushMessage($message, $ttr, $delay, $priority)
            {
                $this->messages[] = $message;
                return (string)count($this->messages);
            }

            public function status($id)
            {
                return self::STATUS_DONE;
            }

            public function work(callable $handler)
            {
                return $this->runWorker($handler);
            }
        };
        $this->behavior = new Log\Behavior();
        $this->queue->attachBehavior('log', $this->behavior);
    }

    protected function tearDown(): void
    {
        \Yii::setLogger(null);
        parent::tearDown();
    }

    public function testPush(): void
    {
        $this->queue->push(new Job());

        $this->assertCount(1, $this->logger->logged);
        [$message, $level, $category] = $this->logger->logged[0];
        $this->assertInstanceOf(Log\JobMessage::class, $message);
        $this->assertSame(Logger::LEVEL_INFO, $level);
        $this->assertSame(Log\JobMessage::class, $category);
        $this->assertSame(['id' => '1', 'name' => Job::class], $message->jsonSerialize());
        $this->assertSame('[1] ' . Job::class, $message->getToken());
    }

    public function testExec(): void
    {
        $this->queue->push(new Job());
        $this->logger->logged = [];

        $this->assertTrue($this->queue->execute('1', $this->queue->messages[0], 300, 1, static::PID));

        $messages = $this->filterLevel(Logger::LEVEL_INFO);
        $this->assertCount(2, $messages);
        foreach ($messages as [$message, , $category]) {
            $this->assertInstanceOf(Log\ExecMessage::class, $message);
            $this->assertSame(Log\ExecMessage::class, $category);
            $this->assertSame(
                ['id' => '1', 'name' => Job::class, 'attempt' => 1, 'pid' => static::PID],
                array_diff_key($message->jsonSerialize(), ['type' => null])
            );
        }

        $token = '[1] ' . Job::class . ' (attempt: 1, PID: ' . static::PID . ')';
        $this->assertSame([$token], array_column($this->filterLevel(Logger::LEVEL_PROFILE_BEGIN), 0));
        $this->assertSame([$token], array_column($this->filterLevel(Logger::LEVEL_PROFILE_END), 0));
        $this->assertSame(1, $this->logger->flushed);
    }

    public function testError(): void
    {
        $this->queue->push(new Job(['error' => 'Something went wrong']));
        $this->logger->logged = [];

        $this->queue->execute('1', $this->queue->messages[0], 300, 1, static::PID);

        $errors = $this->filterLevel(Logger::LEVEL_ERROR);
        $this->assertCount(1, $errors);
        [$error, , $category] = $errors[0];
        $this->assertInstanceOf(Log\Error::class, $error);
        $this->assertSame(Log\Error::class, $category);
        $this->assertSame(
            Job::class . ' Error RuntimeException: Something went wrong',
            $error->getMessage()
        );
        $this->assertSame(7, $error->getCode());
        $this->assertInstanceOf(\RuntimeException::class, $error->getPrevious());
        $this->assertSame(Log\ProfileInterface::TYPE_DONE, $error->getType());
        $this->assertSame(
            [
                'type' => Log\ProfileInterface::TYPE_DONE,
                'id' => '1',
                'name' => Job::class,
                'attempt' => 1,
                'pid' => static::PID,
            ],
            $error->jsonSerialize()
        );
        $this->assertStringContainsString('Additional Information:', (string)$error);

        $token = '[1] ' . Job::class . ' (attempt: 1, PID: ' . static::PID . ')';
        $this->assertContains($token, array_column($this->filterLevel(Logger::LEVEL_PROFILE_END), 0));
    }

    public function testWorker(): void
    {
        $this->queue->work(function (): void {
        });

        $pid = getmypid();
        $messages = $this->filterLevel(Logger::LEVEL_INFO);
        $this->assertCount(2, $messages);
        foreach ($messages as [$message, , $category]) {
            $this->assertInstanceOf(Log\WorkerMessage::class, $message);
            $this->assertSame(Log\WorkerMessage::class, $category);
            $this->assertSame($pid, $message->getPid());
        }
        $this->assertSame(['pid' => $pid, 'status' => Log\WorkerMessage::STATUS_UP], $messages[0][0]->jsonSerialize());
        $this->assertSame(
            ['pid' => $pid, 'status' => Log\WorkerMessage::STATUS_DOWN],
            $messages[1][0]->jsonSerialize()
        );

        $this->assertSame(["Worker {$pid}"], array_column($this->filterLevel(Logger::LEVEL_PROFILE_BEGIN), 0));
        $this->assertSame(["Worker {$pid}"], array_column($this->filterLevel(Logger::LEVEL_PROFILE_END), 0));
        $this->assertSame(1, $this->logger->flushed);
    }

    public function testDisabledAutoFlush(): void
    {
        $this->behavior->autoFlush = false;

        $this->queue->work(function (): void {
        });

        $this->assertNotEmpty($this->filterLevel(Logger::LEVEL_PROFILE_END));
        $this->assertSame(0, $this->logger->flushed);
    }

    private function filterLevel(int $level): array
    {
        return array_values(array_filter(
            $this->logger->logged,
            fn(array $entry): bool => $entry[1] === $level
        ));
    }
}
