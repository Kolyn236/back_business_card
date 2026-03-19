<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Config;

use App\Shared\Application\Exception\NotificationDeliveryException;

final readonly class TelegramBotConfig
{
    public function __construct(
        public string $botToken,
        public string $chatId,
    ) {
        if (trim($this->botToken) === '' || trim($this->chatId) === '') {
            throw new NotificationDeliveryException('Telegram configuration is incomplete.');
        }
    }
}
