<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

final readonly class CollaboratorContext
{
    public const string DEFAULT_USER_ID = 'default-owner';
    public const string DEFAULT_DISPLAY_NAME = 'Default Owner';

    public function __construct(
        public string $userId,
        public string $displayName,
    ) {
    }

    public static function defaults(): self
    {
        return new self(self::DEFAULT_USER_ID, self::DEFAULT_DISPLAY_NAME);
    }
}
