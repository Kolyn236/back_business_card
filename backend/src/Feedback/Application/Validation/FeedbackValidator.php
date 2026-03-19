<?php

declare(strict_types=1);

namespace App\Feedback\Application\Validation;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Shared\Application\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class FeedbackValidator implements FeedbackValidatorInterface
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    public function validate(FeedbackSubmission $submission): void
    {
        $violations = $this->validator->validate($submission);

        if (\count($violations) > 0) {
            throw ValidationException::fromConstraintViolations($violations);
        }
    }
}
