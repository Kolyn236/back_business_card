<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Notification;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Infrastructure\Notification\Formatting\FeedbackNotificationFormatter;
use App\Shared\Application\Exception\NotificationDeliveryException;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final readonly class FeedbackEmailFactory
{
    public function __construct(
        private FeedbackNotificationFormatter $formatter,
        private string $fromAddress,
        private string $fromName,
        private string $toAddress,
        private string $envelopeSender,
    ) {
    }

    public function createMessage(FeedbackSubmission $submission): Email
    {
        $this->assertConfigured();

        return (new Email())
            ->from(new Address($this->fromAddress, $this->fromName))
            ->to($this->toAddress)
            ->replyTo(new Address($submission->email, $submission->name))
            ->subject(sprintf('New offer/project request from %s', $submission->name))
            ->text($this->formatter->formatEmailText($submission))
            ->html($this->formatter->formatEmailHtml($submission));
    }

    public function createEnvelope(): Envelope
    {
        $this->assertConfigured();

        return new Envelope(
            new Address($this->envelopeSender),
            [new Address($this->toAddress)],
        );
    }

    private function assertConfigured(): void
    {
        if (
            trim($this->fromAddress) === ''
            || trim($this->fromName) === ''
            || trim($this->toAddress) === ''
            || trim($this->envelopeSender) === ''
        ) {
            throw new NotificationDeliveryException('Email configuration is incomplete.');
        }
    }
}
