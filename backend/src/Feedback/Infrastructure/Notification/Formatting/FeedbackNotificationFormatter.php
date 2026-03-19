<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Notification\Formatting;

use App\Feedback\Application\Dto\FeedbackSubmission;

final class FeedbackNotificationFormatter
{
    private const TELEGRAM_MESSAGE_MAX_LENGTH = 3500;

    public function formatTelegram(FeedbackSubmission $submission): string
    {
        $lines = [
            '<b>New feedback request</b>',
            '',
            sprintf('<b>Name:</b> %s', $this->escape($submission->name)),
            sprintf('<b>Email:</b> %s', $this->escape($submission->email)),
            sprintf('<b>Company:</b> %s', $this->escape($submission->company ?? 'Not provided')),
            sprintf('<b>Offer type:</b> %s', $this->escape($submission->offerType)),
            sprintf('<b>Budget:</b> %s', $this->escape($submission->budget ?? 'Not provided')),
            sprintf('<b>Timeline:</b> %s', $this->escape($submission->timeline ?? 'Not provided')),
            sprintf('<b>Consent:</b> %s', $submission->consent ? 'Yes' : 'No'),
            '',
            '<b>Message:</b>',
            $this->escape($this->truncateTelegramText($submission->message)),
        ];

        return implode("\n", $lines);
    }

    public function formatEmailText(FeedbackSubmission $submission): string
    {
        $lines = [
            'New feedback request',
            '',
            sprintf('Name: %s', $submission->name),
            sprintf('Email: %s', $submission->email),
            sprintf('Company: %s', $submission->company ?? 'Not provided'),
            sprintf('Offer type: %s', $submission->offerType),
            sprintf('Budget: %s', $submission->budget ?? 'Not provided'),
            sprintf('Timeline: %s', $submission->timeline ?? 'Not provided'),
            sprintf('Consent: %s', $submission->consent ? 'Yes' : 'No'),
            '',
            'Message:',
            $submission->message,
        ];

        return implode("\n", $lines);
    }

    public function formatEmailHtml(FeedbackSubmission $submission): string
    {
        $rows = [
            ['Name', $submission->name],
            ['Email', $submission->email],
            ['Company', $submission->company ?? 'Not provided'],
            ['Offer type', $submission->offerType],
            ['Budget', $submission->budget ?? 'Not provided'],
            ['Timeline', $submission->timeline ?? 'Not provided'],
            ['Consent', $submission->consent ? 'Yes' : 'No'],
        ];

        $details = array_map(
            static fn (array $row): string => sprintf(
                '<tr><th align="left" style="padding:8px;border:1px solid #d7dde5;">%s</th><td style="padding:8px;border:1px solid #d7dde5;">%s</td></tr>',
                htmlspecialchars($row[0], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                nl2br(htmlspecialchars((string) $row[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')),
            ),
            $rows,
        );

        $message = nl2br(htmlspecialchars($submission->message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

        return sprintf(<<<HTML
<html lang="en">
<body style="font-family:Arial,sans-serif;color:#1f2937;">
    <h2 style="margin-bottom:16px;">New feedback request</h2>
    <table cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-bottom:16px;min-width:420px;">
        %s
    </table>
    <h3 style="margin-bottom:8px;">Message</h3>
    <p style="margin:0;line-height:1.5;">%s</p>
</body>
</html>
HTML, implode('', $details), $message);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function truncateTelegramText(string $value): string
    {
        if (mb_strlen($value) <= self::TELEGRAM_MESSAGE_MAX_LENGTH) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, self::TELEGRAM_MESSAGE_MAX_LENGTH - 1)).'…';
    }
}
