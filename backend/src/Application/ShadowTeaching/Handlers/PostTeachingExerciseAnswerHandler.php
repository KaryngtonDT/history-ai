<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching\Handlers;

use App\Application\ShadowTeaching\TeachingBuilder;
use App\Application\ShadowTeaching\TeachingJsonMapper;

final class PostTeachingExerciseAnswerHandler
{
    public function __construct(
        private readonly TeachingBuilder $builder,
        private readonly TeachingJsonMapper $mapper,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, string $exerciseId, array $payload): array
    {
        $answer = is_string($payload['answer'] ?? null) ? trim($payload['answer']) : '';
        $plan = $this->builder->answerExercise($scopeKey, $exerciseId, $answer);

        return $this->mapper->toArray($plan);
    }
}
