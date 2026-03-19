<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Notification;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Port\FeedbackSubmissionProcessorInterface;
use App\Feedback\Infrastructure\Notification\Message\SendTelegramFeedbackMessage;
use App\Shared\Application\Exception\NotificationDeliveryException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class TelegramFeedbackProcessor implements FeedbackSubmissionProcessorInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function process(FeedbackSubmission $submission): void
    {
        try {
            $this->messageBus->dispatch(new SendTelegramFeedbackMessage($submission));
        } catch (ExceptionInterface $exception) {
            throw new NotificationDeliveryException('Telegram notification could not be queued.', 0, $exception);
        }
    }

    public static function getPriority(): int
    {
        return 200;
    }
}
