<?php

declare(strict_types=1);

namespace App\Feedback\Application\Message;

use App\Feedback\Application\Dto\FeedbackSubmission;

final readonly class ProcessFeedbackSubmissionMessage
{
    public function __construct(
        public FeedbackSubmission $submission,
    ) {
    }
}
