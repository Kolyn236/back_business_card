<?php

declare(strict_types=1);

namespace App\Feedback\Application\Response;

final readonly class SubmitFeedbackResult
{
    public function __construct(
        public string $status,
        public string $message,
        public int $statusCode,
    ) {
    }
}
