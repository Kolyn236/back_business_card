<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Infrastructure\Notification\Formatting;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Infrastructure\Notification\Formatting\FeedbackNotificationFormatter;
use PHPUnit\Framework\TestCase;

final class FeedbackNotificationFormatterTest extends TestCase
{
    public function testItFormatsTelegramMessageWithEscaping(): void
    {
        $formatter = new FeedbackNotificationFormatter();

        $message = $formatter->formatTelegram($this->createSubmission());

        self::assertStringContainsString('<b>New feedback request</b>', $message);
        self::assertStringContainsString('&lt;Nikola&gt; &amp; Co', $message);
        self::assertStringContainsString('Need &lt;b&gt;launch&lt;/b&gt; site', $message);
        self::assertStringContainsString('<b>Consent:</b> Yes', $message);
    }

    public function testItTruncatesLongTelegramMessageBody(): void
    {
        $formatter = new FeedbackNotificationFormatter();

        $message = $formatter->formatTelegram(new FeedbackSubmission(
            name: 'Nikola Tesla',
            email: 'nikola@example.com',
            company: 'Acme Studio',
            offerType: 'Landing page',
            budget: '$2k-$5k',
            message: str_repeat('A', 5000),
            timeline: '2-3 weeks',
            consent: true,
        ));

        self::assertLessThan(4096, mb_strlen($message));
        self::assertStringContainsString("…", $message);
    }

    public function testItFormatsPlainTextEmailMessage(): void
    {
        $formatter = new FeedbackNotificationFormatter();

        $message = $formatter->formatEmailText($this->createSubmission());

        self::assertStringContainsString('New feedback request', $message);
        self::assertStringContainsString('Name: <Nikola> & Co', $message);
        self::assertStringContainsString('Company: Not provided', $message);
        self::assertStringContainsString('Message:', $message);
    }

    public function testItFormatsHtmlEmailMessageWithEscaping(): void
    {
        $formatter = new FeedbackNotificationFormatter();

        $message = $formatter->formatEmailHtml($this->createSubmission());

        self::assertStringContainsString('<html lang="en">', $message);
        self::assertStringContainsString('&lt;Nikola&gt; &amp; Co', $message);
        self::assertStringContainsString('Not provided', $message);
        self::assertStringContainsString('Need &lt;b&gt;launch&lt;/b&gt; site', $message);
    }

    private function createSubmission(): FeedbackSubmission
    {
        return new FeedbackSubmission(
            name: '<Nikola> & Co',
            email: 'nikola@example.com',
            company: null,
            offerType: 'Landing page',
            budget: null,
            message: 'Need <b>launch</b> site',
            timeline: null,
            consent: true,
        );
    }
}
