<?php

declare(strict_types=1);

namespace App\Feedback\Application\Service;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Port\FeedbackSubmissionProcessorInterface;
use App\Feedback\Application\Response\SubmitFeedbackResult;
use App\Feedback\Application\Validation\FeedbackValidatorInterface;

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

        foreach ($this->processors as $processor) {
            $processor->process($submission);
        }

        return new SubmitFeedbackResult(
            status: 'ok',
            message: 'Feedback submitted successfully.',
        );
    }
}
