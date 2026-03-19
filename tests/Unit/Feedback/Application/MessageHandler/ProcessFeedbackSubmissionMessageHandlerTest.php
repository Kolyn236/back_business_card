<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Application\MessageHandler;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Message\ProcessFeedbackSubmissionMessage;
use App\Feedback\Application\MessageHandler\ProcessFeedbackSubmissionMessageHandler;
use App\Feedback\Application\Port\FeedbackSubmissionProcessorInterface;
use App\Shared\Application\Exception\NotificationDeliveryException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class ProcessFeedbackSubmissionMessageHandlerTest extends TestCase
{
    public function testItSkipsAlreadyProcessedProcessorOnDuplicateHandling(): void
    {
        $firstProcessor = new FirstInMemoryProcessor();
        $secondProcessor = new SecondInMemoryProcessor();
        $handler = new ProcessFeedbackSubmissionMessageHandler([$firstProcessor, $secondProcessor], new ArrayAdapter());
        $message = new ProcessFeedbackSubmissionMessage($this->createSubmission());

        $handler($message);
        $handler($message);

        self::assertCount(1, $firstProcessor->processed);
        self::assertCount(1, $secondProcessor->processed);
    }

    public function testItRetriesOnlyProcessorsWithoutProcessedMarker(): void
    {
        $firstProcessor = new FirstInMemoryProcessor();
        $secondProcessor = new SecondInMemoryProcessor(true);
        $handler = new ProcessFeedbackSubmissionMessageHandler([$firstProcessor, $secondProcessor], new ArrayAdapter());
        $message = new ProcessFeedbackSubmissionMessage($this->createSubmission());

        try {
            $handler($message);
            self::fail('Expected processor failure.');
        } catch (NotificationDeliveryException) {
        }

        $secondProcessor->shouldFail = false;

        $handler($message);

        self::assertCount(1, $firstProcessor->processed);
        self::assertCount(2, $secondProcessor->processed);
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

abstract class InMemoryProcessor implements FeedbackSubmissionProcessorInterface
{
    /** @var list<FeedbackSubmission> */
    public array $processed = [];

    public function __construct(
        public bool $shouldFail = false,
    ) {
    }

    public function process(FeedbackSubmission $submission): void
    {
        $this->processed[] = $submission;

        if ($this->shouldFail) {
            throw new NotificationDeliveryException('Processor failure.');
        }
    }
}

final class FirstInMemoryProcessor extends InMemoryProcessor
{
}

final class SecondInMemoryProcessor extends InMemoryProcessor
{
}
