<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Notification\MessageHandler;

use App\Feedback\Infrastructure\Notification\Message\SendTelegramFeedbackMessage;
use App\Feedback\Infrastructure\Notification\TelegramMessageSender;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendTelegramFeedbackMessageHandler
{
    public function __construct(
        private TelegramMessageSender $sender,
    ) {
    }

    public function __invoke(SendTelegramFeedbackMessage $message): void
    {
        $this->sender->send($message->submission);
    }
}
