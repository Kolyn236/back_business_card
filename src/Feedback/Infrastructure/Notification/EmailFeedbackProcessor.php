<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Notification;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Port\FeedbackSubmissionProcessorInterface;
use App\Shared\Application\Exception\NotificationDeliveryException;
use Symfony\Component\Mailer\MailerInterface;
use Throwable;

final readonly class EmailFeedbackProcessor implements FeedbackSubmissionProcessorInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private FeedbackEmailFactory $emailFactory,
    ) {
    }

    public function process(FeedbackSubmission $submission): void
    {
        $message = $this->emailFactory->createMessage($submission);
        $envelope = $this->emailFactory->createEnvelope();

        try {
            $this->mailer->send($message, $envelope);
        } catch (Throwable $exception) {
            throw new NotificationDeliveryException('Email notification could not be queued.', 0, $exception);
        }
    }

    public static function getPriority(): int
    {
        return 100;
    }
}
