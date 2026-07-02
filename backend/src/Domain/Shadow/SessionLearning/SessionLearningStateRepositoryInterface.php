<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

use App\Domain\Shadow\ShadowSessionId;

interface SessionLearningStateRepositoryInterface
{
    public function save(SessionLearningState $state): void;

    public function findBySessionId(ShadowSessionId $sessionId): ?SessionLearningState;

    public function deleteBySessionId(ShadowSessionId $sessionId): void;
}
