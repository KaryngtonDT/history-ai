<?php

declare(strict_types=1);

namespace App\Infrastructure\YouTube;

use App\Domain\YouTube\YouTubeCaptionResult;

final class YouTubePendingCaptionStore
{
    public function __construct(
        private readonly string $directory,
    ) {
    }

    public function save(string $videoId, YouTubeCaptionResult $captions): void
    {
        $this->ensureDirectory();
        $path = $this->pathFor($videoId);
        file_put_contents($path, json_encode([
            'kind' => $captions->kind->value,
            'language' => $captions->language,
            'segments' => $captions->segments,
        ], JSON_THROW_ON_ERROR));
    }

    public function load(string $videoId): ?YouTubeCaptionResult
    {
        $path = $this->pathFor($videoId);

        if (!is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);

        if (!is_string($content)) {
            return null;
        }

        try {
            /** @var array<string, mixed> $data */
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        $kind = \App\Domain\YouTube\YouTubeCaptionKind::tryFrom((string) ($data['kind'] ?? ''));

        if (null === $kind || !is_array($data['segments'] ?? null)) {
            return null;
        }

        /** @var list<array{index: int, start: float, end: float, text: string}> $segments */
        $segments = $data['segments'];

        return new YouTubeCaptionResult(
            $kind,
            is_string($data['language'] ?? null) ? $data['language'] : 'unknown',
            $segments,
        );
    }

    public function clear(string $videoId): void
    {
        $path = $this->pathFor($videoId);

        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function pathFor(string $videoId): string
    {
        return rtrim($this->directory, '/\\').'/'.$videoId.'-captions.json';
    }

    private function ensureDirectory(): void
    {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0775, true) && !is_dir($this->directory)) {
            throw new \RuntimeException('Unable to create YouTube caption store directory.');
        }
    }
}
