<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Notification\Message;

use App\Feedback\Application\Dto\FeedbackSubmission;

final readonly class SendTelegramFeedbackMessage
{
    public function __construct(
        public FeedbackSubmission $submission,
    ) {
    }
}
