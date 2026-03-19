<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Application\Service;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Port\FeedbackSubmissionProcessorInterface;
use App\Feedback\Application\Service\SubmitFeedbackService;
use App\Feedback\Application\Validation\FeedbackValidatorInterface;
use App\Shared\Application\Exception\NotificationDeliveryException;
use PHPUnit\Framework\TestCase;

final class SubmitFeedbackServiceTest extends TestCase
{
    public function testItValidatesAndProcessesSubmission(): void
    {
        $validator = new InMemoryValidator();
        $firstProcessor = new InMemoryProcessor();
        $secondProcessor = new InMemoryProcessor();

        $service = new SubmitFeedbackService($validator, [$firstProcessor, $secondProcessor]);
        $submission = $this->createSubmission();

        $result = $service->handle($submission);

        self::assertTrue($validator->wasCalled);
        self::assertSame($submission, $validator->submission);
        self::assertCount(1, $firstProcessor->processed);
        self::assertCount(1, $secondProcessor->processed);
        self::assertSame('ok', $result->status);
        self::assertSame('Feedback submitted successfully.', $result->message);
    }

    public function testItContinuesProcessingAndThrowsFirstFailure(): void
    {
        $validator = new InMemoryValidator();
        $failingProcessor = new InMemoryProcessor(true);
        $nextProcessor = new InMemoryProcessor();

        $service = new SubmitFeedbackService($validator, [$failingProcessor, $nextProcessor]);

        $this->expectException(NotificationDeliveryException::class);
        $this->expectExceptionMessage('Processor failure.');

        try {
            $service->handle($this->createSubmission());
        } finally {
            self::assertCount(1, $failingProcessor->processed);
            self::assertCount(1, $nextProcessor->processed);
        }
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

final class InMemoryProcessor implements FeedbackSubmissionProcessorInterface
{
    /** @var list<FeedbackSubmission> */
    public array $processed = [];

    public function __construct(
        private readonly bool $shouldFail = false,
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
