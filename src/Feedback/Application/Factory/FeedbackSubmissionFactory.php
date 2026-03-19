<?php

declare(strict_types=1);

namespace App\Feedback\Application\Factory;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Shared\Application\Exception\InvalidRequestPayloadException;

final class FeedbackSubmissionFactory
{
    /**
     * @param array<string, mixed> $payload
     */
    public function fromArray(array $payload): FeedbackSubmission
    {
        return new FeedbackSubmission(
            name: $this->requireString($payload, 'name'),
            email: $this->requireString($payload, 'email'),
            company: $this->optionalString($payload, 'company'),
            offerType: $this->requireString($payload, 'offerType'),
            budget: $this->optionalString($payload, 'budget'),
            message: $this->requireString($payload, 'message'),
            timeline: $this->optionalString($payload, 'timeline'),
            consent: $this->requireBool($payload, 'consent'),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requireString(array $payload, string $field): string
    {
        if (!array_key_exists($field, $payload)) {
            throw InvalidRequestPayloadException::forField($field, 'Field is required.');
        }

        if (!is_string($payload[$field])) {
            throw InvalidRequestPayloadException::forField($field, 'Field must be a string.');
        }

        return trim($payload[$field]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function optionalString(array $payload, string $field): ?string
    {
        if (!array_key_exists($field, $payload) || $payload[$field] === null || $payload[$field] === '') {
            return null;
        }

        if (!is_string($payload[$field])) {
            throw InvalidRequestPayloadException::forField($field, 'Field must be a string.');
        }

        $value = trim($payload[$field]);

        return $value !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requireBool(array $payload, string $field): bool
    {
        if (!array_key_exists($field, $payload)) {
            throw InvalidRequestPayloadException::forField($field, 'Field is required.');
        }

        if (!is_bool($payload[$field])) {
            throw InvalidRequestPayloadException::forField($field, 'Field must be a boolean.');
        }

        return $payload[$field];
    }
}
