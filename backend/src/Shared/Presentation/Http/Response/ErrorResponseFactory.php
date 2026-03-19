<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ErrorResponseFactory
{
    /**
     * @param array<string, list<string>> $errors
     */
    public function create(int $statusCode, string $code, string $message, array $errors = [], ?string $summary = null): JsonResponse
    {
        $payload = [
            'status' => 'error',
            'code' => $code,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        if ($summary !== null && $summary !== '') {
            $payload['summary'] = $summary;
        }

        return new JsonResponse($payload, $statusCode);
    }
}
