<?php

declare(strict_types=1);

namespace App\Domain\Library;

interface LibraryItemRepositoryInterface
{
    public function save(LibraryItem $item): void;

    public function findById(LibraryItemId $id): ?LibraryItem;

    /**
     * @return list<LibraryItem>
     */
    public function findAll(): array;
}
