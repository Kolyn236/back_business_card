<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\EventSubscriber;

use App\Shared\Application\Exception\InvalidJsonException;
use App\Shared\Application\Exception\InvalidRequestPayloadException;
use App\Shared\Application\Exception\NotificationDeliveryException;
use App\Shared\Application\Exception\UnsupportedMediaTypeException;
use App\Shared\Application\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

use App\Shared\Presentation\Http\Response\ErrorResponseFactory;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ErrorResponseFactory $errorResponseFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $throwable = $event->getThrowable();

        if ($throwable instanceof InvalidJsonException || $throwable instanceof InvalidRequestPayloadException) {
            $event->setResponse($this->errorResponseFactory->create(
                Response::HTTP_BAD_REQUEST,
                'invalid_request',
                $throwable->getMessage(),
            ));

            return;
        }

        if ($throwable instanceof UnsupportedMediaTypeException) {
            $event->setResponse($this->errorResponseFactory->create(
                Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
                'unsupported_media_type',
                $throwable->getMessage(),
            ));

            return;
        }

        if ($throwable instanceof ValidationException) {
            $event->setResponse($this->errorResponseFactory->create(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'validation_failed',
                $throwable->getMessage(),
                $throwable->errors(),
            ));

            return;
        }

        if ($throwable instanceof NotificationDeliveryException) {
            $event->setResponse($this->errorResponseFactory->create(
                Response::HTTP_BAD_GATEWAY,
                'notification_delivery_failed',
                $throwable->getMessage(),
            ));

            return;
        }

        if ($throwable instanceof HttpExceptionInterface) {
            $event->setResponse($this->errorResponseFactory->create(
                $throwable->getStatusCode(),
                'http_error',
                $throwable->getMessage() !== '' ? $throwable->getMessage() : Response::$statusTexts[$throwable->getStatusCode()] ?? 'HTTP error.',
            ));

            return;
        }

        if ($throwable instanceof Throwable) {
            $event->setResponse($this->errorResponseFactory->create(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'internal_server_error',
                'An unexpected server error occurred.',
            ));
        }
    }
}
