<?php

declare(strict_types=1);

namespace App\Feedback\Application\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class FeedbackSubmission
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 180)]
        public string $email,
        #[Assert\Length(max: 180)]
        public ?string $company,
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $offerType,
        #[Assert\Length(max: 100)]
        public ?string $budget,
        #[Assert\NotBlank]
        #[Assert\Length(min: 10, max: 5000)]
        public string $message,
        #[Assert\Length(max: 100)]
        public ?string $timeline,
        #[Assert\IsTrue(message: 'Consent must be accepted.')]
        public bool $consent,
    ) {
    }
}
