<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Collection;

use App\Domain\Library\Exception\InvalidLibraryItemException;
use App\Domain\Library\LibraryItemId;
use App\Presentation\Http\Request\Collection\Exception\InvalidCollectionRequestException;

final readonly class AssignLibraryItemToCollectionRequest
{
    public function __construct(
        public string $libraryItemId,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (!isset($payload['libraryItemId']) || !is_string($payload['libraryItemId'])) {
            throw new InvalidCollectionRequestException('Library item id is required.');
        }

        try {
            new LibraryItemId($payload['libraryItemId']);
        } catch (InvalidLibraryItemException) {
            throw new InvalidCollectionRequestException('Library item id is invalid.');
        }

        return new self($payload['libraryItemId']);
    }
}
