<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Notification;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Port\FeedbackSubmissionProcessorInterface;
use App\Feedback\Infrastructure\Notification\Formatting\FeedbackNotificationFormatter;
use App\Shared\Application\Exception\NotificationDeliveryException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final readonly class EmailFeedbackProcessor implements FeedbackSubmissionProcessorInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private FeedbackNotificationFormatter $formatter,
        private string $fromAddress,
        private string $toAddress,
    ) {
    }

    public function process(FeedbackSubmission $submission): void
    {
        if (trim($this->fromAddress) === '' || trim($this->toAddress) === '') {
            throw new NotificationDeliveryException('Email configuration is incomplete.');
        }

        $message = (new Email())
            ->from(new Address($this->fromAddress, 'Business Card Backend'))
            ->to($this->toAddress)
            ->replyTo($submission->email)
            ->subject(sprintf('New offer/project request from %s', $submission->name))
            ->text($this->formatter->formatEmailText($submission))
            ->html($this->formatter->formatEmailHtml($submission));

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $exception) {
            throw new NotificationDeliveryException('Email notification failed.', 0, $exception);
        }
    }
}
