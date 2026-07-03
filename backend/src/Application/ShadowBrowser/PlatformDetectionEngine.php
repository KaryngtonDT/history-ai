<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser;

use App\Domain\ShadowBrowser\BrowserPlatform;

final class PlatformDetectionEngine
{
    public function detect(string $url): BrowserPlatform
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        if ('' === $host) {
            return BrowserPlatform::Unknown;
        }

        return match (true) {
            $this->hostEndsWith($host, 'youtube.com'),
            'youtu.be' === $host,
            $this->hostEndsWith($host, 'youtu.be') => BrowserPlatform::Youtube,
            $this->hostEndsWith($host, 'wikipedia.org') => BrowserPlatform::Wikipedia,
            'developer.mozilla.org' === $host => BrowserPlatform::Mdn,
            $this->hostEndsWith($host, 'symfony.com') && str_starts_with($path, '/doc') => BrowserPlatform::SymfonyDocs,
            'php.net' === $host,
            'www.php.net' === $host => BrowserPlatform::PhpDocs,
            'github.com' === $host => BrowserPlatform::Github,
            'gitlab.com' === $host => BrowserPlatform::Gitlab,
            'stackoverflow.com' === $host => BrowserPlatform::Stackoverflow,
            'reddit.com' === $host,
            $this->hostEndsWith($host, '.reddit.com') => BrowserPlatform::Reddit,
            str_ends_with($path, '.pdf') => BrowserPlatform::PdfViewer,
            default => BrowserPlatform::Unknown,
        };
    }

    public function extractHost(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ('' === $host) {
            return '';
        }

        if (str_starts_with($host, 'www.')) {
            return substr($host, 4);
        }

        return $host;
    }

    private function hostEndsWith(string $host, string $suffix): bool
    {
        return str_ends_with($host, $suffix);
    }
}
