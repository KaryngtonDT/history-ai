<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

use App\Domain\Workspace\Exception\InvalidProjectException;

final readonly class ProjectId
{
    public function __construct(public string $value)
    {
        if (!self::isValid($value)) {
            throw new InvalidProjectException('Project id must be a valid UUID.');
        }
    }

    public static function generate(): self
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0F | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3F | 0x80);

        return new self(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4)));
    }

    public static function isValid(string $value): bool
    {
        return 1 === preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value,
        );
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
