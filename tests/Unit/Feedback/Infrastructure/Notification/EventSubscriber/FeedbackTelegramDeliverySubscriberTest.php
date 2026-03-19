<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Infrastructure\Notification\EventSubscriber;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Infrastructure\Notification\EventSubscriber\FeedbackTelegramDeliverySubscriber;
use App\Feedback\Infrastructure\Notification\Message\SendTelegramFeedbackMessage;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageRetriedEvent;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

final class FeedbackTelegramDeliverySubscriberTest extends TestCase
{
    public function testItLogsRetryForQueuedTelegramMessage(): void
    {
        $handler = new TestHandler();
        $subscriber = new FeedbackTelegramDeliverySubscriber(new Logger('telegram', [$handler]));

        $event = new WorkerMessageRetriedEvent(
            new Envelope($this->createMessage(), [new RedeliveryStamp(2)]),
            'async',
        );

        $subscriber->onMessageRetried($event);

        self::assertTrue($handler->hasWarningThatContains('Telegram delivery will be retried.'));
        $records = $handler->getRecords();
        self::assertSame(2, $records[0]['context']['retry_count']);
        self::assertSame('Nikola Tesla', $records[0]['context']['name']);
        self::assertSame('Landing page', $records[0]['context']['offer_type']);
    }

    public function testItLogsPermanentFailureOnlyAfterRetriesAreExhausted(): void
    {
        $handler = new TestHandler();
        $subscriber = new FeedbackTelegramDeliverySubscriber(new Logger('telegram', [$handler]));

        $event = new WorkerMessageFailedEvent(
            new Envelope($this->createMessage(), [new RedeliveryStamp(5)]),
            'failed',
            new RuntimeException('Telegram API returned status 500.'),
        );

        $subscriber->onMessageFailed($event);

        self::assertTrue($handler->hasErrorThatContains('Telegram delivery failed permanently.'));
        $records = $handler->getRecords();
        self::assertSame(RuntimeException::class, $records[0]['context']['exception_class']);
        self::assertSame('Telegram API returned status 500.', $records[0]['context']['exception_message']);
    }

    public function testItSkipsFailureLogWhenMessageWillBeRetried(): void
    {
        $handler = new TestHandler();
        $subscriber = new FeedbackTelegramDeliverySubscriber(new Logger('telegram', [$handler]));

        $event = new WorkerMessageFailedEvent(
            new Envelope($this->createMessage()),
            'async',
            new RuntimeException('Telegram API timeout'),
        );
        $event->setForRetry();

        $subscriber->onMessageFailed($event);

        self::assertFalse($handler->hasErrorRecords());
    }

    private function createMessage(): SendTelegramFeedbackMessage
    {
        return new SendTelegramFeedbackMessage(new FeedbackSubmission(
            name: 'Nikola Tesla',
            email: 'nikola@example.com',
            company: 'Acme Studio',
            offerType: 'Landing page',
            budget: '$2k-$5k',
            message: 'Need a launch website for a new product line.',
            timeline: '2-3 weeks',
            consent: true,
        ));
    }
}
