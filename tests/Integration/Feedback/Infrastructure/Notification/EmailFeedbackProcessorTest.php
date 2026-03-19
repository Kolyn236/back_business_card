<?php

declare(strict_types=1);

namespace App\Tests\Integration\Feedback\Infrastructure\Notification;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Infrastructure\Notification\EmailFeedbackProcessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class EmailFeedbackProcessorTest extends KernelTestCase
{
    public function testItQueuesEmailWithConfiguredSenderReplyToAndEnvelopeSender(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var EmailFeedbackProcessor $processor */
        $processor = $container->get(EmailFeedbackProcessor::class);

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
        self::assertInstanceOf(SendEmailMessage::class, $queuedMessage);

        $email = $queuedMessage->getMessage();
        self::assertSame('New offer/project request from Nikola Tesla', $email->getSubject());
        self::assertSame('no-reply@example.com', $email->getFrom()[0]->getAddress());
        self::assertSame('Business Card Backend', $email->getFrom()[0]->getName());
        self::assertSame('owner@example.com', $email->getTo()[0]->getAddress());
        self::assertSame('nikola@example.com', $email->getReplyTo()[0]->getAddress());
        self::assertSame('Nikola Tesla', $email->getReplyTo()[0]->getName());
        self::assertStringContainsString('Need a launch website for a new product line.', $email->getTextBody());
        self::assertStringContainsString('<h2 style="margin-bottom:16px;">New feedback request</h2>', $email->getHtmlBody());

        $envelope = $queuedMessage->getEnvelope();
        self::assertNotNull($envelope);
        self::assertSame('bounces@example.com', $envelope->getSender()->getAddress());
        self::assertSame('owner@example.com', $envelope->getRecipients()[0]->getAddress());
    }
}
