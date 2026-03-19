<?php

declare(strict_types=1);

namespace App\Tests\Functional\Feedback;

use App\Tests\Double\Feedback\TestFeedbackSubmissionProcessor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SubmitFeedbackControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestFeedbackSubmissionProcessor::reset();
    }

    public function testItReturnsSuccessForValidPayload(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/feedback',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'name' => 'Nikola Tesla',
                'email' => 'nikola@example.com',
                'company' => 'Acme Studio',
                'offerType' => 'Landing page',
                'budget' => '$2k-$5k',
                'message' => 'Need a launch website for a new product line.',
                'timeline' => '2-3 weeks',
                'consent' => true,
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'status' => 'ok',
                'message' => 'Feedback submitted successfully.',
            ], JSON_THROW_ON_ERROR),
            $client->getResponse()->getContent(),
        );
        self::assertCount(1, TestFeedbackSubmissionProcessor::$processedSubmissions);
    }

    public function testItReturnsBadRequestForInvalidJson(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/feedback',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: '{',
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'status' => 'error',
                'code' => 'invalid_request',
                'message' => 'Request body must contain valid JSON.',
            ], JSON_THROW_ON_ERROR),
            $client->getResponse()->getContent(),
        );
    }

    public function testItReturnsUnsupportedMediaTypeForNonJsonRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/feedback',
            server: [
                'CONTENT_TYPE' => 'text/plain',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: 'plain text body',
        );

        self::assertResponseStatusCodeSame(415);
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'status' => 'error',
                'code' => 'unsupported_media_type',
                'message' => 'Content-Type must be application/json.',
            ], JSON_THROW_ON_ERROR),
            $client->getResponse()->getContent(),
        );
    }

    public function testItReturnsValidationErrorsForInvalidPayload(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/feedback',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'name' => '',
                'email' => 'bad-email',
                'offerType' => 'Landing page',
                'message' => 'short',
                'consent' => false,
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(422);
        self::assertJson($client->getResponse()->getContent());

        /** @var array{status:string,code:string,message:string,errors:array<string,list<string>>} $payload */
        $payload = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('error', $payload['status']);
        self::assertSame('validation_failed', $payload['code']);
        self::assertSame('Validation failed.', $payload['message']);
        self::assertArrayHasKey('name', $payload['errors']);
        self::assertArrayHasKey('email', $payload['errors']);
        self::assertArrayHasKey('message', $payload['errors']);
        self::assertArrayHasKey('consent', $payload['errors']);
    }

    public function testItReturnsBadGatewayWhenNotificationDeliveryFails(): void
    {
        TestFeedbackSubmissionProcessor::$shouldFail = true;

        $client = static::createClient();

        $client->request(
            'POST',
            '/api/feedback',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'name' => 'Nikola Tesla',
                'email' => 'nikola@example.com',
                'offerType' => 'Landing page',
                'message' => 'Need a launch website for a new product line.',
                'consent' => true,
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(502);
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'status' => 'error',
                'code' => 'notification_delivery_failed',
                'message' => 'Test notification failure.',
            ], JSON_THROW_ON_ERROR),
            $client->getResponse()->getContent(),
        );
    }
}
