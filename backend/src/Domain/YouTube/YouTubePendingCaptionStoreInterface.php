<?php

declare(strict_types=1);

namespace App\Domain\YouTube;

interface YouTubePendingCaptionStoreInterface
{
    public function save(string $videoId, YouTubeCaptionResult $captions): void;

    public function load(string $videoId): ?YouTubeCaptionResult;

    public function clear(string $videoId): void;
}
