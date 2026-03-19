<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Presentation\Http\EventSubscriber;

use App\Shared\Application\Exception\InvalidJsonException;
use App\Shared\Application\Exception\NotificationDeliveryException;
use App\Shared\Application\Exception\UnsupportedMediaTypeException;
use App\Shared\Application\Exception\ValidationException;
use App\Shared\Presentation\Http\EventSubscriber\ApiExceptionSubscriber;
use App\Shared\Presentation\Http\Response\ErrorResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ApiExceptionSubscriberTest extends TestCase
{
    public function testItMapsInvalidJsonToBadRequest(): void
    {
        $response = $this->handle('/api/feedback', new InvalidJsonException('Request body must contain valid JSON.'));

        self::assertSame(400, $response?->getStatusCode());
        self::assertSame(
            '{"status":"error","code":"invalid_request","message":"Request body must contain valid JSON."}',
            $response?->getContent(),
        );
    }

    public function testItMapsUnsupportedMediaType(): void
    {
        $response = $this->handle('/api/feedback', new UnsupportedMediaTypeException('Content-Type must be application/json.'));

        self::assertSame(415, $response?->getStatusCode());
        self::assertSame(
            '{"status":"error","code":"unsupported_media_type","message":"Content-Type must be application\/json."}',
            $response?->getContent(),
        );
    }

    public function testItMapsValidationExceptionWithErrors(): void
    {
        $response = $this->handle('/api/feedback', new ValidationException([
            'email' => ['Укажи корректный email.'],
        ]));

        self::assertSame(422, $response?->getStatusCode());
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
            $response?->getContent(),
        );
    }

    public function testItMapsNotificationFailure(): void
    {
        $response = $this->handle('/api/feedback', new NotificationDeliveryException('Telegram notification failed.'));

        self::assertSame(502, $response?->getStatusCode());
        self::assertSame(
            '{"status":"error","code":"notification_delivery_failed","message":"Telegram notification failed."}',
            $response?->getContent(),
        );
    }

    public function testItMapsHttpExceptions(): void
    {
        $response = $this->handle('/api/feedback', new NotFoundHttpException('No route found.'));

        self::assertSame(404, $response?->getStatusCode());
        self::assertSame(
            '{"status":"error","code":"http_error","message":"No route found."}',
            $response?->getContent(),
        );
    }

    public function testItIgnoresNonApiPaths(): void
    {
        $response = $this->handle('/health', new InvalidJsonException('Request body must contain valid JSON.'));

        self::assertNull($response);
    }

    public function testItMapsUnknownThrowablesToInternalServerError(): void
    {
        $response = $this->handle('/api/feedback', new \RuntimeException('boom'));

        self::assertSame(500, $response?->getStatusCode());
        self::assertSame(
            '{"status":"error","code":"internal_server_error","message":"An unexpected server error occurred."}',
            $response?->getContent(),
        );
    }

    private function handle(string $path, \Throwable $throwable): ?Response
    {
        $subscriber = new ApiExceptionSubscriber(new ErrorResponseFactory());
        $request = Request::create($path);
        $kernel = $this->createMock(HttpKernelInterface::class);

        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $throwable,
        );

        $subscriber->onKernelException($event);

        return $event->getResponse();
    }
}
