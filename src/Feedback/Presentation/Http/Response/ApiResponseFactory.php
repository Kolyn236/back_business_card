<?php

declare(strict_types=1);

namespace App\Feedback\Presentation\Http\Response;

use App\Feedback\Application\Response\SubmitFeedbackResult;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponseFactory
{
    public function success(SubmitFeedbackResult $result): JsonResponse
    {
        return new JsonResponse([
            'status' => $result->status,
            'message' => $result->message,
        ], Response::HTTP_OK);
    }
}
