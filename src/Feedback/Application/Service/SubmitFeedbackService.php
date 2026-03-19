<?php

declare(strict_types=1);

namespace App\Feedback\Application\Service;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Port\FeedbackSubmissionProcessorInterface;
use App\Feedback\Application\Response\SubmitFeedbackResult;
use App\Feedback\Application\Validation\FeedbackValidatorInterface;
use App\Shared\Application\Exception\NotificationDeliveryException;

final readonly class SubmitFeedbackService
{
    /**
     * @param iterable<FeedbackSubmissionProcessorInterface> $processors
     */
    public function __construct(
        private FeedbackValidatorInterface $validator,
        private iterable $processors,
    ) {
    }

    public function handle(FeedbackSubmission $submission): SubmitFeedbackResult
    {
        $this->validator->validate($submission);

        $firstFailure = null;

        foreach ($this->processors as $processor) {
            try {
                $processor->process($submission);
            } catch (NotificationDeliveryException $exception) {
                $firstFailure ??= $exception;
            }
        }

        if ($firstFailure instanceof NotificationDeliveryException) {
            throw $firstFailure;
        }

        return new SubmitFeedbackResult(
            status: 'ok',
            message: 'Feedback submitted successfully.',
        );
    }
}
