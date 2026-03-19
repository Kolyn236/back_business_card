<?php

declare(strict_types=1);

namespace App\Shared\Application\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use RuntimeException;

final class ValidationException extends RuntimeException
{
    /**
     * @param array<string, list<string>> $errors
     */
    public function __construct(
        private readonly array $errors,
        string $message = 'Validation failed.',
    ) {
        parent::__construct($message);
    }

    public static function fromConstraintViolations(ConstraintViolationListInterface $violations): self
    {
        $errors = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $field = $violation->getPropertyPath() !== '' ? $violation->getPropertyPath() : 'payload';
            $errors[$field][] = $violation->getMessage();
        }

        return new self($errors);
    }

    /**
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
