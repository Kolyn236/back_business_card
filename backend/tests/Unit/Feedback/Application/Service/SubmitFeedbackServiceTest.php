<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Application\Service;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Message\ProcessFeedbackSubmissionMessage;
use App\Feedback\Application\Service\SubmitFeedbackService;
use App\Feedback\Application\Validation\FeedbackValidatorInterface;
use App\Shared\Application\Exception\NotificationDeliveryException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class SubmitFeedbackServiceTest extends TestCase
{
    public function testItValidatesAndQueuesSubmission(): void
    {
        $validator = new InMemoryValidator();
        $messageBus = new InMemoryMessageBus();

        $service = new SubmitFeedbackService($validator, $messageBus);
        $submission = $this->createSubmission();

        $result = $service->handle($submission);

        self::assertTrue($validator->wasCalled);
        self::assertSame($submission, $validator->submission);
        self::assertCount(1, $messageBus->dispatchedMessages);
        self::assertInstanceOf(ProcessFeedbackSubmissionMessage::class, $messageBus->dispatchedMessages[0]);
        self::assertSame($submission, $messageBus->dispatchedMessages[0]->submission);
        self::assertSame('ok', $result->status);
        self::assertSame('Feedback accepted for processing.', $result->message);
        self::assertSame(202, $result->statusCode);
    }

    public function testItThrowsWhenQueueDispatchFails(): void
    {
        $validator = new InMemoryValidator();
        $messageBus = new InMemoryMessageBus(true);

        $service = new SubmitFeedbackService($validator, $messageBus);

        $this->expectException(NotificationDeliveryException::class);
        $this->expectExceptionMessage('Feedback submission could not be queued.');

        $service->handle($this->createSubmission());
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

final class InMemoryValidator implements FeedbackValidatorInterface
{
    public bool $wasCalled = false;
    public ?FeedbackSubmission $submission = null;

    public function validate(FeedbackSubmission $submission): void
    {
        $this->wasCalled = true;
        $this->submission = $submission;
    }
}

final class InMemoryMessageBus implements MessageBusInterface
{
    /** @var list<ProcessFeedbackSubmissionMessage> */
    public array $dispatchedMessages = [];

    public function __construct(
        private readonly bool $shouldFail = false,
    ) {
    }

    public function dispatch(object $message, array $stamps = []): Envelope
    {
        if ($this->shouldFail) {
            throw new class () extends \RuntimeException implements ExceptionInterface {
            };
        }

        if (!$message instanceof ProcessFeedbackSubmissionMessage) {
            throw new \InvalidArgumentException(sprintf('Unexpected message class "%s".', $message::class));
        }

        $this->dispatchedMessages[] = $message;

        return new Envelope($message, $stamps);
    }
}
