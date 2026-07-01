<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

use App\Domain\Video\VideoId;

interface ShadowSessionRepositoryInterface
{
    public function save(ShadowSession $session): void;

    public function findById(ShadowSessionId $id): ?ShadowSession;

    /**
     * @return list<ShadowSession>
     */
    public function findByVideoId(VideoId $videoId): array;
}
