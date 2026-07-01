<?php

declare(strict_types=1);

namespace App\Application\Shadow\DTO;

use App\Domain\Shadow\ShadowChallenge;

final readonly class ShadowChallengeResult
{
    public function __construct(
        public string $questionText,
        public ?string $suggestedAnswer,
    ) {
    }

    public static function fromDomain(ShadowChallenge $challenge): self
    {
        return new self(
            questionText: $challenge->questionText(),
            suggestedAnswer: $challenge->suggestedAnswer(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = ['questionText' => $this->questionText];

        if (null !== $this->suggestedAnswer) {
            $data['suggestedAnswer'] = $this->suggestedAnswer;
        }

        return $data;
    }
}
