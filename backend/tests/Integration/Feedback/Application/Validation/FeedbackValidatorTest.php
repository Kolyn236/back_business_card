<?php

declare(strict_types=1);

namespace App\Tests\Integration\Feedback\Application\Validation;

use App\Feedback\Application\Dto\FeedbackSubmission;
use App\Feedback\Application\Validation\FeedbackValidator;
use App\Shared\Application\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class FeedbackValidatorTest extends KernelTestCase
{
    public function testItAcceptsValidSubmission(): void
    {
        self::bootKernel();

        $validator = new FeedbackValidator(self::getContainer()->get(ValidatorInterface::class));

        $validator->validate(new FeedbackSubmission(
            name: 'Nikola Tesla',
            email: 'nikola@example.com',
            company: 'Acme Studio',
            offerType: 'Landing page',
            budget: '$2k-$5k',
            message: 'Need a new launch website for a product.',
            timeline: '2-3 weeks',
            consent: true,
        ));

        self::assertTrue(true);
    }

    public function testItThrowsValidationExceptionForInvalidSubmission(): void
    {
        self::bootKernel();

        $validator = new FeedbackValidator(self::getContainer()->get(ValidatorInterface::class));

        try {
            $validator->validate(new FeedbackSubmission(
                name: '',
                email: 'bad-email',
                company: null,
                offerType: '',
                budget: null,
                message: 'short',
                timeline: null,
                consent: false,
            ));

            self::fail('ValidationException was expected.');
        } catch (ValidationException $exception) {
            self::assertSame('Validation failed.', $exception->getMessage());
            self::assertSame('Проверь поля формы и попробуй снова.', $exception->summary());
            self::assertArrayHasKey('name', $exception->errors());
            self::assertArrayHasKey('email', $exception->errors());
            self::assertArrayHasKey('offerType', $exception->errors());
            self::assertArrayHasKey('message', $exception->errors());
            self::assertArrayHasKey('consent', $exception->errors());
            self::assertSame(['Укажи имя.'], $exception->errors()['name']);
            self::assertSame(['Укажи корректный email.'], $exception->errors()['email']);
            self::assertSame(['Выбери тип предложения.'], $exception->errors()['offerType']);
            self::assertSame(['Описание слишком короткое. Минимум 10 символов.'], $exception->errors()['message']);
            self::assertSame(['Нужно согласие на обработку данных.'], $exception->errors()['consent']);
        }
    }
}
