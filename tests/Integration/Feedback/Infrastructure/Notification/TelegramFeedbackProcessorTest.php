<?php

declare(strict_types=1);

namespace App\Tests\Integration\Feedback\Infrastructure\Notification;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Infrastructure\Notification\Message\SendTelegramFeedbackMessage;
use App\Feedback\Infrastructure\Notification\TelegramFeedbackProcessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class TelegramFeedbackProcessorTest extends KernelTestCase
{
    public function testItQueuesTelegramMessage(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var TelegramFeedbackProcessor $processor */
        $processor = $container->get(TelegramFeedbackProcessor::class);

        /** @var InMemoryTransport $transport */
        $transport = $container->get('messenger.transport.async');
        $transport->reset();

        $processor->process(new FeedbackSubmission(
            name: 'Nikola Tesla',
            email: 'nikola@example.com',
            company: 'Acme Studio',
            offerType: 'Landing page',
            budget: '$2k-$5k',
            message: 'Need a launch website for a new product line.',
            timeline: '2-3 weeks',
            consent: true,
        ));

        $sent = $transport->getSent();

        self::assertCount(1, $sent);
        self::assertContainsOnlyInstancesOf(Envelope::class, $sent);

        $queuedMessage = $sent[0]->getMessage();
        self::assertInstanceOf(SendTelegramFeedbackMessage::class, $queuedMessage);
        self::assertSame('Nikola Tesla', $queuedMessage->submission->name);
        self::assertSame('nikola@example.com', $queuedMessage->submission->email);
    }
}
