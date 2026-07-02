<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class TeachingExercise
{
    /**
     * @param list<string> $options
     */
    public function __construct(
        private string $id,
        private ExerciseType $type,
        private string $question,
        private array $options,
        private string $correctAnswer,
        private string $explanation,
        private string $objectiveKey,
        private ExerciseStatus $status,
    ) {
    }

    public static function create(
        ExerciseType $type,
        string $question,
        array $options,
        string $correctAnswer,
        string $explanation,
        string $objectiveKey,
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            $type,
            $question,
            $options,
            $correctAnswer,
            $explanation,
            $objectiveKey,
            ExerciseStatus::Pending,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): ExerciseType
    {
        return $this->type;
    }

    public function question(): string
    {
        return $this->question;
    }

    /** @return list<string> */
    public function options(): array
    {
        return $this->options;
    }

    public function correctAnswer(): string
    {
        return $this->correctAnswer;
    }

    public function explanation(): string
    {
        return $this->explanation;
    }

    public function objectiveKey(): string
    {
        return $this->objectiveKey;
    }

    public function status(): ExerciseStatus
    {
        return $this->status;
    }

    public function withStatus(ExerciseStatus $status): self
    {
        return new self(
            $this->id,
            $this->type,
            $this->question,
            $this->options,
            $this->correctAnswer,
            $this->explanation,
            $this->objectiveKey,
            $status,
        );
    }
}
