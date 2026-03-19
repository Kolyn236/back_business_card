<?php

declare(strict_types=1);

namespace App\Tests\Double\Feedback;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Port\FeedbackSubmissionProcessorInterface;
use App\Shared\Application\Exception\NotificationDeliveryException;

final class TestFeedbackSubmissionProcessor implements FeedbackSubmissionProcessorInterface
{
    public static bool $shouldFail = false;

    /** @var list<FeedbackSubmission> */
    public static array $processedSubmissions = [];

    public static function reset(): void
    {
        self::$shouldFail = false;
        self::$processedSubmissions = [];
    }

    public function process(FeedbackSubmission $submission): void
    {
        self::$processedSubmissions[] = $submission;

        if (self::$shouldFail) {
            throw new NotificationDeliveryException('Test notification failure.');
        }
    }
}
