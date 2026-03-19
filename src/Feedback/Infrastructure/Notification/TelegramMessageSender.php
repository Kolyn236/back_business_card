<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Notification;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Infrastructure\Config\TelegramBotConfig;
use App\Feedback\Infrastructure\Notification\Formatting\FeedbackNotificationFormatter;
use App\Shared\Application\Exception\NotificationDeliveryException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class TelegramMessageSender
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private TelegramBotConfig $config,
        private FeedbackNotificationFormatter $formatter,
    ) {
    }

    public function send(FeedbackSubmission $submission): void
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
            $payload = $response->toArray(false);

            if ($statusCode >= 400 || ($payload['ok'] ?? false) !== true) {
                $failure = $this->buildApiFailure($statusCode, $payload);

                if ($this->shouldRetry($statusCode)) {
                    throw $failure;
                }

                throw new UnrecoverableMessageHandlingException($failure->getMessage(), 0, $failure);
            }
        } catch (UnrecoverableMessageHandlingException $exception) {
            throw $exception;
        } catch (ExceptionInterface $exception) {
            throw new NotificationDeliveryException('Telegram notification failed.', 0, $exception);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildApiFailure(int $statusCode, array $payload): NotificationDeliveryException
    {
        $description = is_string($payload['description'] ?? null) ? $payload['description'] : null;
        $errorCode = is_int($payload['error_code'] ?? null) ? $payload['error_code'] : null;

        if ($description !== null && $errorCode !== null) {
            return new NotificationDeliveryException(sprintf(
                'Telegram API returned an unsuccessful response: %d %s',
                $errorCode,
                $description,
            ));
        }

        if ($statusCode >= 400) {
            return new NotificationDeliveryException(sprintf('Telegram API returned status %d.', $statusCode));
        }

        return new NotificationDeliveryException('Telegram API returned an unsuccessful response.');
    }

    private function shouldRetry(int $statusCode): bool
    {
        return $statusCode === 429 || $statusCode >= 500;
    }
}
