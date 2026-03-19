<?php

declare(strict_types=1);

namespace App\Feedback\Application\Service;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Message\ProcessFeedbackSubmissionMessage;
use App\Feedback\Application\Response\SubmitFeedbackResult;
use App\Feedback\Application\Validation\FeedbackValidatorInterface;
use App\Shared\Application\Exception\NotificationDeliveryException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class SubmitFeedbackService
{
    public function __construct(
        private FeedbackValidatorInterface $validator,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function handle(FeedbackSubmission $submission): SubmitFeedbackResult
    {
        $this->validator->validate($submission);

        try {
            $this->messageBus->dispatch(new ProcessFeedbackSubmissionMessage($submission));
        } catch (ExceptionInterface $exception) {
            throw new NotificationDeliveryException('Feedback submission could not be queued.', 0, $exception);
        }

        return new SubmitFeedbackResult(
            status: 'ok',
            message: 'Feedback accepted for processing.',
            statusCode: Response::HTTP_ACCEPTED,
        );
    }
}
