<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Infrastructure\Notification\EventSubscriber;

use App\Feedback\Infrastructure\Notification\EventSubscriber\FeedbackEmailDeliverySubscriber;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Mailer\Envelope as MailerEnvelope;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageRetriedEvent;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

final class FeedbackEmailDeliverySubscriberTest extends TestCase
{
    public function testItLogsRetryForQueuedEmail(): void
    {
        $handler = new TestHandler();
        $subscriber = new FeedbackEmailDeliverySubscriber(new Logger('mailer', [$handler]));

        $message = $this->createEmailMessage();
        $event = new WorkerMessageRetriedEvent(
            new Envelope($message, [new RedeliveryStamp(2)]),
            'async',
        );

        $subscriber->onMessageRetried($event);

        self::assertTrue($handler->hasWarningThatContains('Email delivery will be retried.'));
        $records = $handler->getRecords();
        self::assertSame(2, $records[0]['context']['retry_count']);
        self::assertSame('Feedback from site', $records[0]['context']['subject']);
        self::assertSame(['"Nikola Tesla" <nikola@example.com>'], $records[0]['context']['reply_to']);
    }

    public function testItLogsPermanentFailureOnlyAfterRetriesAreExhausted(): void
    {
        $handler = new TestHandler();
        $subscriber = new FeedbackEmailDeliverySubscriber(new Logger('mailer', [$handler]));

        $message = $this->createEmailMessage();
        $event = new WorkerMessageFailedEvent(
            new Envelope($message, [new RedeliveryStamp(5)]),
            'failed',
            new RuntimeException('SMTP timeout'),
        );

        $subscriber->onMessageFailed($event);

        self::assertTrue($handler->hasErrorThatContains('Email delivery failed permanently.'));
        $records = $handler->getRecords();
        self::assertSame(RuntimeException::class, $records[0]['context']['exception_class']);
        self::assertSame('SMTP timeout', $records[0]['context']['exception_message']);
    }

    public function testItSkipsFailureLogWhenMessageWillBeRetried(): void
    {
        $handler = new TestHandler();
        $subscriber = new FeedbackEmailDeliverySubscriber(new Logger('mailer', [$handler]));

        $event = new WorkerMessageFailedEvent(
            new Envelope($this->createEmailMessage()),
            'async',
            new RuntimeException('SMTP timeout'),
        );
        $event->setForRetry();

        $subscriber->onMessageFailed($event);

        self::assertFalse($handler->hasErrorRecords());
    }

    private function createEmailMessage(): SendEmailMessage
    {
        $email = (new Email())
            ->from(new Address('no-reply@example.com', 'Business Card Backend'))
            ->to('owner@example.com')
            ->replyTo(new Address('nikola@example.com', 'Nikola Tesla'))
            ->subject('Feedback from site')
            ->text('Plain text body');

        return new SendEmailMessage(
            $email,
            new MailerEnvelope(
                new Address('bounces@example.com'),
                [new Address('owner@example.com')],
            ),
        );
    }
}
