<?php

declare(strict_types=1);

namespace App\Feedback\Presentation\Http\Request;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Factory\FeedbackSubmissionFactory;
use App\Shared\Application\Exception\InvalidJsonException;
use App\Shared\Application\Exception\InvalidRequestPayloadException;
use App\Shared\Application\Exception\UnsupportedMediaTypeException;
use JsonException;
use Symfony\Component\HttpFoundation\Request;

final readonly class FeedbackRequestMapper
{
    public function __construct(
        private FeedbackSubmissionFactory $factory,
    ) {
    }

    public function fromRequest(Request $request): FeedbackSubmission
    {
        if ($request->getContentTypeFormat() !== 'json') {
            throw new UnsupportedMediaTypeException('Content-Type must be application/json.');
        }

        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidJsonException('Request body must contain valid JSON.', 0, $exception);
        }

        if (!is_array($payload)) {
            throw new InvalidRequestPayloadException('Request body must be a JSON object.');
        }

        return $this->factory->fromArray($payload);
    }
}
