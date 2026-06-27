<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Collection;

use App\Presentation\Http\Request\Collection\Exception\InvalidCollectionRequestException;

final readonly class CreateCollectionRequest
{
    public function __construct(
        public string $name,
        public string $description,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (!isset($payload['name']) || !is_string($payload['name'])) {
            throw new InvalidCollectionRequestException('Name is required.');
        }

        if ('' === trim($payload['name'])) {
            throw new InvalidCollectionRequestException('Name cannot be empty.');
        }

        $description = '';
        if (array_key_exists('description', $payload)) {
            if (!is_string($payload['description'])) {
                throw new InvalidCollectionRequestException('Description must be a string.');
            }

            $description = $payload['description'];
        }

        return new self($payload['name'], $description);
    }
}
