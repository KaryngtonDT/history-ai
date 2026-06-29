<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\SelectedDocument;

final readonly class SelectedDocumentResult
{
    public function __construct(
        public string $contentId,
    ) {
    }

    public static function fromDomain(SelectedDocument $document): self
    {
        return new self($document->contentId()->value);
    }
}
