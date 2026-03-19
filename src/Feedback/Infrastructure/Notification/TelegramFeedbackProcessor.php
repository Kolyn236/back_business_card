<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Notification;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Port\FeedbackSubmissionProcessorInterface;
use App\Feedback\Infrastructure\Config\TelegramBotConfig;
use App\Feedback\Infrastructure\Notification\Formatting\FeedbackNotificationFormatter;
use App\Shared\Application\Exception\NotificationDeliveryException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class TelegramFeedbackProcessor implements FeedbackSubmissionProcessorInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private TelegramBotConfig $config,
        private FeedbackNotificationFormatter $formatter,
    ) {
    }

    public function process(FeedbackSubmission $submission): void
    {
        try {
            $response = $this->httpClient->request('POST', sprintf(
                'https://api.telegram.org/bot%s/sendMessage',
                $this->config->botToken,
            ), [
                'json' => [
                    'chat_id' => $this->config->chatId,
                    'text' => $this->formatter->formatTelegram($submission),
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ],
                'timeout' => 10,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                throw new NotificationDeliveryException(sprintf('Telegram API returned status %d.', $statusCode));
            }

            $payload = $response->toArray(false);

            if (($payload['ok'] ?? false) !== true) {
                throw new NotificationDeliveryException('Telegram API returned an unsuccessful response.');
            }
        } catch (ExceptionInterface $exception) {
            throw new NotificationDeliveryException('Telegram notification failed.', 0, $exception);
        }
    }
}
