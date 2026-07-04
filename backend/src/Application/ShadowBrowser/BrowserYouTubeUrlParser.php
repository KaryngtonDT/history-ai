<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser;

final class BrowserYouTubeUrlParser
{
    public function extractVideoKey(string $url): ?string
    {
        $trimmed = trim($url);

        if ('' === $trimmed) {
            return null;
        }

        if (preg_match('/[?&]v=([\w-]{11})/', $trimmed, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('#youtu\.be/([\w-]{11})#', $trimmed, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('#youtube\.com/shorts/([\w-]{11})#', $trimmed, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    public function urlsMatch(string $left, string $right): bool
    {
        $leftKey = $this->extractVideoKey($left);
        $rightKey = $this->extractVideoKey($right);

        if (null !== $leftKey && null !== $rightKey) {
            return $leftKey === $rightKey;
        }

        return trim($left) === trim($right);
    }
}
