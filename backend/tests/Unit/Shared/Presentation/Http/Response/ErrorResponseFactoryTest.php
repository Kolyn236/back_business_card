<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Presentation\Http\Response;

use App\Shared\Presentation\Http\Response\ErrorResponseFactory;
use PHPUnit\Framework\TestCase;

final class ErrorResponseFactoryTest extends TestCase
{
    public function testItBuildsErrorResponseWithoutFieldErrors(): void
    {
        $factory = new ErrorResponseFactory();
        $response = $factory->create(400, 'invalid_request', 'Request body must contain valid JSON.');

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(
            '{"status":"error","code":"invalid_request","message":"Request body must contain valid JSON."}',
            $response->getContent(),
        );
    }

    public function testItBuildsErrorResponseWithFieldErrors(): void
    {
        $factory = new ErrorResponseFactory();
        $response = $factory->create(422, 'validation_failed', 'Validation failed.', [
            'email' => ['Укажи корректный email.'],
        ], 'Проверь поля формы и попробуй снова.');

        self::assertSame(422, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'status' => 'error',
                'code' => 'validation_failed',
                'message' => 'Validation failed.',
                'errors' => [
                    'email' => ['Укажи корректный email.'],
                ],
                'summary' => 'Проверь поля формы и попробуй снова.',
            ], JSON_THROW_ON_ERROR),
            $response->getContent(),
        );
    }
}
