<?php

declare(strict_types=1);

namespace App\Feedback\Application\Port;

use App\Feedback\Application\Dto\FeedbackSubmission;

interface FeedbackSubmissionProcessorInterface
{
    public function process(FeedbackSubmission $submission): void;
}
