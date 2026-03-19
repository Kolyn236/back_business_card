<?php

declare(strict_types=1);

namespace App\Feedback\Application\MessageHandler;

use App\Feedback\Application\Message\ProcessFeedbackSubmissionMessage;
use App\Feedback\Application\Port\FeedbackSubmissionProcessorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Cache\CacheItemPoolInterface;

#[AsMessageHandler]
final readonly class ProcessFeedbackSubmissionMessageHandler
{
    private const PROCESSED_MARKER_TTL = 604800;

    /**
     * @param iterable<FeedbackSubmissionProcessorInterface> $processors
     */
    public function __construct(
        private iterable $processors,
        private CacheItemPoolInterface $cache,
    ) {
    }

    public function __invoke(ProcessFeedbackSubmissionMessage $message): void
    {
        $submissionFingerprint = $this->buildSubmissionFingerprint($message);

        foreach ($this->processors as $processor) {
            $cacheItem = $this->cache->getItem($this->buildProcessorCacheKey($processor, $submissionFingerprint));

            if ($cacheItem->isHit()) {
                continue;
            }

            $processor->process($message->submission);

            $cacheItem->set(true);
            $cacheItem->expiresAfter(self::PROCESSED_MARKER_TTL);
            $this->cache->save($cacheItem);
        }
    }

    private function buildSubmissionFingerprint(ProcessFeedbackSubmissionMessage $message): string
    {
        return hash('sha256', json_encode([
            'name' => $message->submission->name,
            'email' => $message->submission->email,
            'company' => $message->submission->company,
            'offerType' => $message->submission->offerType,
            'budget' => $message->submission->budget,
            'message' => $message->submission->message,
            'timeline' => $message->submission->timeline,
            'consent' => $message->submission->consent,
        ], JSON_THROW_ON_ERROR));
    }

    private function buildProcessorCacheKey(FeedbackSubmissionProcessorInterface $processor, string $submissionFingerprint): string
    {
        return sprintf(
            'feedback_submission.%s.%s',
            hash('sha256', $processor::class),
            $submissionFingerprint,
        );
    }
}
