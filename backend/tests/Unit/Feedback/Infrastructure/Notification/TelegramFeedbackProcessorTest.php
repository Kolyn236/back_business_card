<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Infrastructure\Notification;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Infrastructure\Notification\Message\SendTelegramFeedbackMessage;
use App\Feedback\Infrastructure\Notification\TelegramFeedbackProcessor;
use App\Shared\Application\Exception\NotificationDeliveryException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;

final class TelegramFeedbackProcessorTest extends TestCase
{
    public function testItQueuesTelegramDeliveryMessage(): void
    {
        $messageBus = new InMemoryMessageBus();
        $processor = new TelegramFeedbackProcessor($messageBus);
        $submission = $this->createSubmission();

        $processor->process($submission);

        self::assertCount(1, $messageBus->messages);
        self::assertInstanceOf(SendTelegramFeedbackMessage::class, $messageBus->messages[0]);
        self::assertSame($submission, $messageBus->messages[0]->submission);
    }

    public function testItWrapsQueueFailures(): void
    {
        $processor = new TelegramFeedbackProcessor(new FailingMessageBus());

        $this->expectException(NotificationDeliveryException::class);
        $this->expectExceptionMessage('Telegram notification could not be queued.');

        $processor->process($this->createSubmission());
    }

    private function createSubmission(): FeedbackSubmission
    {
        return new FeedbackSubmission(
            name: 'Nikola Tesla',
            email: 'nikola@example.com',
            company: 'Acme Studio',
            offerType: 'Landing page',
            budget: '$2k-$5k',
            message: 'Need a new launch website for a product.',
            timeline: '2-3 weeks',
            consent: true,
        );
    }
}

final class InMemoryMessageBus implements MessageBusInterface
{
    /** @var list<object> */
    public array $messages = [];

    public function dispatch(object $message, array $stamps = []): Envelope
    {
        $this->messages[] = $message;

        return new Envelope($message, $stamps);
    }
}

final class FailingMessageBus implements MessageBusInterface
{
    public function dispatch(object $message, array $stamps = []): Envelope
    {
        throw new TransportException('queue unavailable');
    }
}
