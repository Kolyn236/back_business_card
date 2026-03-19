<?php

declare(strict_types=1);

namespace App\Feedback\Application\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class FeedbackSubmission
{
    public function __construct(
        #[Assert\NotBlank(message: 'Укажи имя.')]
        #[Assert\Length(max: 120, maxMessage: 'Имя должно быть не длиннее {{ limit }} символов.')]
        public string $name,
        #[Assert\NotBlank(message: 'Укажи email.')]
        #[Assert\Email(message: 'Укажи корректный email.')]
        #[Assert\Length(max: 180, maxMessage: 'Email должен быть не длиннее {{ limit }} символов.')]
        public string $email,
        #[Assert\Length(max: 180, maxMessage: 'Название компании должно быть не длиннее {{ limit }} символов.')]
        public ?string $company,
        #[Assert\NotBlank(message: 'Выбери тип предложения.')]
        #[Assert\Length(max: 100, maxMessage: 'Тип предложения должен быть не длиннее {{ limit }} символов.')]
        public string $offerType,
        #[Assert\Length(max: 100, maxMessage: 'Бюджет должен быть не длиннее {{ limit }} символов.')]
        public ?string $budget,
        #[Assert\NotBlank(message: 'Опиши предложение или задачу.')]
        #[Assert\Length(
            min: 10,
            max: 5000,
            minMessage: 'Описание слишком короткое. Минимум {{ limit }} символов.',
            maxMessage: 'Описание должно быть не длиннее {{ limit }} символов.',
        )]
        public string $message,
        #[Assert\Length(max: 100, maxMessage: 'Сроки должны быть не длиннее {{ limit }} символов.')]
        public ?string $timeline,
        #[Assert\IsTrue(message: 'Нужно согласие на обработку данных.')]
        public bool $consent,
    ) {
    }
}
