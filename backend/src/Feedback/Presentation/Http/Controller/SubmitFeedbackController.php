<?php

declare(strict_types=1);

namespace App\Feedback\Presentation\Http\Controller;

use App\Feedback\Application\Service\SubmitFeedbackService;
use App\Feedback\Presentation\Http\Request\FeedbackRequestMapper;
use App\Feedback\Presentation\Http\Response\ApiResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SubmitFeedbackController
{
    #[Route('/api/feedback', name: 'api_feedback_submit', methods: ['POST'])]
    public function __invoke(
        Request $request,
        FeedbackRequestMapper $requestMapper,
        SubmitFeedbackService $submitFeedbackService,
        ApiResponseFactory $responseFactory,
    ) {
        $submission = $requestMapper->fromRequest($request);
        $result = $submitFeedbackService->handle($submission);

        return $responseFactory->success($result);
    }
}
