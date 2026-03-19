<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Notification\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageRetriedEvent;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

final readonly class FeedbackEmailDeliverySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageRetriedEvent::class => 'onMessageRetried',
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }

    public function onMessageRetried(WorkerMessageRetriedEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();
        if (!$message instanceof SendEmailMessage) {
            return;
        }

        $this->logger->warning('Email delivery will be retried.', [
            'receiver' => $event->getReceiverName(),
            'retry_count' => RedeliveryStamp::getRetryCountFromEnvelope($event->getEnvelope()),
            ...$this->extractMessageContext($message->getMessage()),
        ]);
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();
        if (!$message instanceof SendEmailMessage || $event->willRetry()) {
            return;
        }

        $this->logger->error('Email delivery failed permanently.', [
            'receiver' => $event->getReceiverName(),
            'retry_count' => RedeliveryStamp::getRetryCountFromEnvelope($event->getEnvelope()),
            'exception_class' => $event->getThrowable()::class,
            'exception_message' => $event->getThrowable()->getMessage(),
            ...$this->extractMessageContext($message->getMessage()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractMessageContext(RawMessage $message): array
    {
        if (!$message instanceof Email) {
            return [
                'message_class' => $message::class,
            ];
        }

        return [
            'message_class' => $message::class,
            'subject' => $message->getSubject(),
            'from' => array_map(
                static fn ($address): string => $address->toString(),
                $message->getFrom(),
            ),
            'to' => array_map(
                static fn ($address): string => $address->toString(),
                $message->getTo(),
            ),
            'reply_to' => array_map(
                static fn ($address): string => $address->toString(),
                $message->getReplyTo(),
            ),
        ];
    }
}
