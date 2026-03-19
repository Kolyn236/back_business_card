<?php

declare(strict_types=1);

namespace App\Feedback\Application\Validation;

use App\Feedback\Application\Dto\FeedbackSubmission;

interface FeedbackValidatorInterface
{
    public function validate(FeedbackSubmission $submission): void;
}
