<?php

declare(strict_types=1);

namespace App\Tests\Unit\Feedback\Application\Factory;

use App\Feedback\Application\Factory\FeedbackSubmissionFactory;
use App\Shared\Application\Exception\InvalidRequestPayloadException;
use PHPUnit\Framework\TestCase;

final class FeedbackSubmissionFactoryTest extends TestCase
{
    private FeedbackSubmissionFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new FeedbackSubmissionFactory();
    }

    public function testItCreatesSubmissionFromValidPayload(): void
    {
        $submission = $this->factory->fromArray([
            'name' => '  Nikola Tesla  ',
            'email' => '  nikola@example.com ',
            'company' => ' Acme Studio ',
            'offerType' => ' Landing page ',
            'budget' => ' $2k-$5k ',
            'message' => ' Need a new launch website for a product. ',
            'timeline' => ' 2-3 weeks ',
            'consent' => true,
        ]);

        self::assertSame('Nikola Tesla', $submission->name);
        self::assertSame('nikola@example.com', $submission->email);
        self::assertSame('Acme Studio', $submission->company);
        self::assertSame('Landing page', $submission->offerType);
        self::assertSame('$2k-$5k', $submission->budget);
        self::assertSame('Need a new launch website for a product.', $submission->message);
        self::assertSame('2-3 weeks', $submission->timeline);
        self::assertTrue($submission->consent);
    }

    public function testItConvertsEmptyOptionalFieldsToNull(): void
    {
        $submission = $this->factory->fromArray([
            'name' => 'Nikola Tesla',
            'email' => 'nikola@example.com',
            'company' => '',
            'offerType' => 'Landing page',
            'budget' => null,
            'message' => 'Need a new launch website for a product.',
            'timeline' => '   ',
            'consent' => true,
        ]);

        self::assertNull($submission->company);
        self::assertNull($submission->budget);
        self::assertNull($submission->timeline);
    }

    public function testItRejectsMissingRequiredField(): void
    {
        $this->expectException(InvalidRequestPayloadException::class);
        $this->expectExceptionMessage('name: Field is required.');

        $this->factory->fromArray([
            'email' => 'nikola@example.com',
            'offerType' => 'Landing page',
            'message' => 'Need a new launch website for a product.',
            'consent' => true,
        ]);
    }

    public function testItRejectsInvalidStringFieldType(): void
    {
        $this->expectException(InvalidRequestPayloadException::class);
        $this->expectExceptionMessage('name: Field must be a string.');

        $this->factory->fromArray([
            'name' => 123,
            'email' => 'nikola@example.com',
            'offerType' => 'Landing page',
            'message' => 'Need a new launch website for a product.',
            'consent' => true,
        ]);
    }

    public function testItRejectsInvalidBooleanFieldType(): void
    {
        $this->expectException(InvalidRequestPayloadException::class);
        $this->expectExceptionMessage('consent: Field must be a boolean.');

        $this->factory->fromArray([
            'name' => 'Nikola Tesla',
            'email' => 'nikola@example.com',
            'offerType' => 'Landing page',
            'message' => 'Need a new launch website for a product.',
            'consent' => 'true',
        ]);
    }
}
