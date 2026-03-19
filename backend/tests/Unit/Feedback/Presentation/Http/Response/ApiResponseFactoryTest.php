<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Presentation\Http\Response;

use App\Feedback\Application\Response\SubmitFeedbackResult;
use App\Feedback\Presentation\Http\Response\ApiResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponseFactoryTest extends TestCase
{
    public function testItBuildsSuccessJsonResponse(): void
    {
        $factory = new ApiResponseFactory();
        $response = $factory->success(new SubmitFeedbackResult('ok', 'Feedback accepted for processing.', Response::HTTP_ACCEPTED));

        self::assertSame(202, $response->getStatusCode());
        self::assertSame('{"status":"ok","message":"Feedback accepted for processing."}', $response->getContent());
    }
}
