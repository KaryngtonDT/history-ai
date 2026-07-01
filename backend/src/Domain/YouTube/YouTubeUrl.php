<?php

declare(strict_types=1);

namespace App\Domain\YouTube;

use App\Domain\YouTube\Exception\InvalidYouTubeException;

final readonly class YouTubeUrl
{
    private const string PATTERN = '/^https?:\/\/(?:www\.|m\.)?(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/)|youtu\.be\/)[\w-]{11}(?:[?&].*)?$/i';

    public function __construct(public string $value)
    {
        self::assertValid($value);
    }

    public static function assertValid(string $url): void
    {
        $trimmed = trim($url);

        if ('' === $trimmed) {
            throw new InvalidYouTubeException('YouTube URL cannot be empty.');
        }

        if (1 !== preg_match(self::PATTERN, $trimmed)) {
            throw new InvalidYouTubeException('YouTube URL is not supported. Use a valid youtube.com or youtu.be link.');
        }
    }

    public static function isValid(string $url): bool
    {
        try {
            self::assertValid($url);

            return true;
        } catch (InvalidYouTubeException) {
            return false;
        }
    }
}
