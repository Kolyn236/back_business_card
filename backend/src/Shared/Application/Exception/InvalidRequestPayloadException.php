<?php

declare(strict_types=1);

namespace App\Shared\Application\Exception;

use RuntimeException;

final class InvalidRequestPayloadException extends RuntimeException
{
    public static function forField(string $field, string $message): self
    {
        return new self(sprintf('%s: %s', $field, $message));
    }
}
