<?php

declare(strict_types=1);

namespace App\Feedback\Infrastructure\Notification\EventSubscriber;

use App\Feedback\Infrastructure\Notification\Message\SendTelegramFeedbackMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageRetriedEvent;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

final readonly class FeedbackTelegramDeliverySubscriber implements EventSubscriberInterface
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
        if (!$message instanceof SendTelegramFeedbackMessage) {
            return;
        }

        $this->logger->warning('Telegram delivery will be retried.', [
            'receiver' => $event->getReceiverName(),
            'retry_count' => RedeliveryStamp::getRetryCountFromEnvelope($event->getEnvelope()),
            ...$this->extractMessageContext($message),
        ]);
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();
        if (!$message instanceof SendTelegramFeedbackMessage || $event->willRetry()) {
            return;
        }

        $this->logger->error('Telegram delivery failed permanently.', [
            'receiver' => $event->getReceiverName(),
            'retry_count' => RedeliveryStamp::getRetryCountFromEnvelope($event->getEnvelope()),
            'exception_class' => $event->getThrowable()::class,
            'exception_message' => $event->getThrowable()->getMessage(),
            ...$this->extractMessageContext($message),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractMessageContext(SendTelegramFeedbackMessage $message): array
    {
        return [
            'name' => $message->submission->name,
            'email' => $message->submission->email,
            'offer_type' => $message->submission->offerType,
        ];
    }
}
