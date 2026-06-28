<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

use App\Domain\Artifact\Artifact;

final class Chunker
{
    public function chunk(Artifact $artifact): ChunkCollection
    {
        $segments = $this->extractSegments($artifact->content()->value());

        /** @var list<Chunk> $chunks */
        $chunks = [];
        $position = 0;

        foreach ($segments as $segment) {
            $trimmed = trim($segment);

            if ('' === $trimmed) {
                continue;
            }

            $chunkPosition = new ChunkPosition($position);
            $chunks[] = new Chunk(
                ChunkId::derive($artifact->id(), $chunkPosition),
                $artifact->id(),
                ChunkText::fromString($trimmed),
                $chunkPosition,
            );
            ++$position;
        }

        return new ChunkCollection($chunks);
    }

    /**
     * @return list<string>
     */
    private function extractSegments(string $markdown): array
    {
        $lines = explode("\n", $markdown);

        if (!$this->containsLevel2Heading($lines)) {
            return [$markdown];
        }

        /** @var list<string> $segments */
        $segments = [];
        /** @var list<string> $current */
        $current = [];

        foreach ($lines as $line) {
            if ($this->isLevel2Heading($line)) {
                if ([] !== $current) {
                    $segments[] = implode("\n", $current);
                    $current = [];
                }
            }

            $current[] = $line;
        }

        if ([] !== $current) {
            $segments[] = implode("\n", $current);
        }

        return $segments;
    }

    /**
     * @param list<string> $lines
     */
    private function containsLevel2Heading(array $lines): bool
    {
        foreach ($lines as $line) {
            if ($this->isLevel2Heading($line)) {
                return true;
            }
        }

        return false;
    }

    private function isLevel2Heading(string $line): bool
    {
        return 1 === preg_match('/^## (?!#)/', $line);
    }
}
