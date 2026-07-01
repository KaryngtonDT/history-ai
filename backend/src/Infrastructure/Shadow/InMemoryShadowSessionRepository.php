<?php

declare(strict_types=1);

namespace App\Infrastructure\Shadow;

use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\Video\VideoId;

final class InMemoryShadowSessionRepository implements ShadowSessionRepositoryInterface
{
    /** @var array<string, ShadowSession> */
    private array $sessions = [];

    public function save(ShadowSession $session): void
    {
        $this->sessions[$session->id()->value] = $session;
    }

    public function findById(ShadowSessionId $id): ?ShadowSession
    {
        return $this->sessions[$id->value] ?? null;
    }

    public function findByVideoId(VideoId $videoId): array
    {
        return array_values(array_filter(
            $this->sessions,
            static fn (ShadowSession $session): bool => $session->videoId()->value === $videoId->value,
        ));
    }

    public function clear(): void
    {
        $this->sessions = [];
    }
}
