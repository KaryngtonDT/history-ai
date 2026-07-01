<?php

declare(strict_types=1);

namespace App\Domain\Collaboration;

use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use DateTimeImmutable;

final readonly class Organization
{
    public function __construct(
        private OrganizationId $id,
        private string $name,
        private DateTimeImmutable $createdAt,
    ) {
        if ('' === trim($name)) {
            throw new InvalidWorkspaceMemberException('Organization name cannot be empty.');
        }
    }

    public static function create(OrganizationId $id, string $name): self
    {
        return new self($id, trim($name), new DateTimeImmutable());
    }

    public function id(): OrganizationId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
