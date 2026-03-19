<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Presentation\Http\Request;

use App\Feedback\Application\Factory\FeedbackSubmissionFactory;
use App\Feedback\Presentation\Http\Request\FeedbackRequestMapper;
use App\Shared\Application\Exception\InvalidJsonException;
use App\Shared\Application\Exception\InvalidRequestPayloadException;
use App\Shared\Application\Exception\UnsupportedMediaTypeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class FeedbackRequestMapperTest extends TestCase
{
    private FeedbackRequestMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new FeedbackRequestMapper(new FeedbackSubmissionFactory());
    }

    public function testItMapsValidJsonRequestToSubmission(): void
    {
        $request = Request::create(
            '/api/feedback',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'name' => 'Nikola Tesla',
                'email' => 'nikola@example.com',
                'company' => 'Acme Studio',
                'offerType' => 'Landing page',
                'budget' => '$2k-$5k',
                'message' => 'Need a new launch website for a product.',
                'timeline' => '2-3 weeks',
                'consent' => true,
            ], JSON_THROW_ON_ERROR),
        );

        $submission = $this->mapper->fromRequest($request);

        self::assertSame('Nikola Tesla', $submission->name);
        self::assertSame('nikola@example.com', $submission->email);
        self::assertTrue($submission->consent);
    }

    public function testItRejectsUnsupportedMediaType(): void
    {
        $this->expectException(UnsupportedMediaTypeException::class);
        $this->expectExceptionMessage('Content-Type must be application/json.');

        $request = Request::create(
            '/api/feedback',
            'POST',
            server: ['CONTENT_TYPE' => 'text/plain'],
            content: 'plain text',
        );

        $this->mapper->fromRequest($request);
    }

    public function testItRejectsInvalidJson(): void
    {
        $this->expectException(InvalidJsonException::class);
        $this->expectExceptionMessage('Request body must contain valid JSON.');

        $request = Request::create(
            '/api/feedback',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{',
        );

        $this->mapper->fromRequest($request);
    }

    public function testItRejectsJsonArrayPayload(): void
    {
        $this->expectException(InvalidRequestPayloadException::class);
        $this->expectExceptionMessage('Request body must be a JSON object.');

        $request = Request::create(
            '/api/feedback',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['unexpected', 'list'], JSON_THROW_ON_ERROR),
        );

        $this->mapper->fromRequest($request);
    }
}
