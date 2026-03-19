<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Infrastructure\Notification;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Infrastructure\Config\TelegramBotConfig;
use App\Feedback\Infrastructure\Notification\Formatting\FeedbackNotificationFormatter;
use App\Feedback\Infrastructure\Notification\TelegramMessageSender;
use App\Shared\Application\Exception\NotificationDeliveryException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final class TelegramMessageSenderTest extends TestCase
{
    public function testItSendsTelegramMessage(): void
    {
        $requests = [];
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = [$method, $url, $options];

            return new MockResponse('{"ok":true,"result":{"message_id":1}}', [
                'http_code' => 200,
                'response_headers' => ['content-type' => 'application/json'],
            ]);
        });

        $sender = new TelegramMessageSender(
            $httpClient,
            new TelegramBotConfig('123456:token', '-1001234567890'),
            new FeedbackNotificationFormatter(),
        );

        $sender->send($this->createSubmission());

        self::assertCount(1, $requests);
        self::assertSame('POST', $requests[0][0]);
        self::assertSame('https://api.telegram.org/bot123456:token/sendMessage', $requests[0][1]);
        self::assertStringContainsString('"chat_id":"-1001234567890"', $requests[0][2]['body']);
        self::assertStringContainsString('"parse_mode":"HTML"', $requests[0][2]['body']);
    }

    public function testItThrowsRecoverableFailureForTemporaryTelegramApiError(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('{"ok":false,"error_code":500,"description":"Internal Server Error"}', [
                'http_code' => 500,
                'response_headers' => ['content-type' => 'application/json'],
            ]),
        ]);

        $sender = new TelegramMessageSender(
            $httpClient,
            new TelegramBotConfig('123456:token', '-1001234567890'),
            new FeedbackNotificationFormatter(),
        );

        $this->expectException(NotificationDeliveryException::class);
        $this->expectExceptionMessage('Telegram API returned an unsuccessful response: 500 Internal Server Error');

        $sender->send($this->createSubmission());
    }

    public function testItThrowsUnrecoverableFailureForPermanentTelegramApiError(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('{"ok":false,"error_code":400,"description":"Bad Request: chat not found"}', [
                'http_code' => 400,
                'response_headers' => ['content-type' => 'application/json'],
            ]),
        ]);

        $sender = new TelegramMessageSender(
            $httpClient,
            new TelegramBotConfig('123456:token', '-1001234567890'),
            new FeedbackNotificationFormatter(),
        );

        $this->expectException(UnrecoverableMessageHandlingException::class);
        $this->expectExceptionMessage('Telegram API returned an unsuccessful response: 400 Bad Request: chat not found');

        $sender->send($this->createSubmission());
    }

    public function testItWrapsTransportExceptions(): void
    {
        $httpClient = new MockHttpClient(static function (): never {
            throw new TransportException('timeout');
        });

        $sender = new TelegramMessageSender(
            $httpClient,
            new TelegramBotConfig('123456:token', '-1001234567890'),
            new FeedbackNotificationFormatter(),
        );

        $this->expectException(NotificationDeliveryException::class);
        $this->expectExceptionMessage('Telegram notification failed.');

        $sender->send($this->createSubmission());
    }

    private function createSubmission(): FeedbackSubmission
    {
        return new FeedbackSubmission(
            name: 'Nikola Tesla',
            email: 'nikola@example.com',
            company: 'Acme Studio',
            offerType: 'Landing page',
            budget: '$2k-$5k',
            message: 'Need a new launch website for a product.',
            timeline: '2-3 weeks',
            consent: true,
        );
    }
}
