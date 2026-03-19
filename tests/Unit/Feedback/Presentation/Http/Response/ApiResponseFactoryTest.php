<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Presentation\Http\Response;

use App\Feedback\Application\Response\SubmitFeedbackResult;
use App\Feedback\Presentation\Http\Response\ApiResponseFactory;
use PHPUnit\Framework\TestCase;

final class ApiResponseFactoryTest extends TestCase
{
    public function testItBuildsSuccessJsonResponse(): void
    {
        $factory = new ApiResponseFactory();
        $response = $factory->success(new SubmitFeedbackResult('ok', 'Feedback submitted successfully.'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"status":"ok","message":"Feedback submitted successfully."}', $response->getContent());
    }
}
