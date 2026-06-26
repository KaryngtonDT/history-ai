<?php

declare(strict_types=1);

namespace App\Domain\Content;

interface ContentRepositoryInterface
{
    public function save(Content $content): void;

    public function findById(ContentId $id): ?Content;

    /**
     * @return list<Content>
     */
    public function findAll(): array;
}
