<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidConversationDocumentException;
use App\Domain\Content\ContentId;

final readonly class SelectedDocumentCollection
{
    /** @var list<SelectedDocument> */
    private array $documents;

    /**
     * @param list<SelectedDocument> $documents
     */
    public function __construct(array $documents = [])
    {
        $this->documents = $this->deduplicatePreservingOrder($documents);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public static function fromContentId(ContentId $contentId): self
    {
        return new self([new SelectedDocument($contentId)]);
    }

    /**
     * @return list<SelectedDocument>
     */
    public function all(): array
    {
        return $this->documents;
    }

    public function count(): int
    {
        return count($this->documents);
    }

    public function contains(ContentId $contentId): bool
    {
        foreach ($this->documents as $document) {
            if ($document->contentId()->equals($contentId)) {
                return true;
            }
        }

        return false;
    }

    public function add(SelectedDocument $document): self
    {
        if ($this->contains($document->contentId())) {
            return $this;
        }

        return new self([...$this->documents, $document]);
    }

    public function remove(ContentId $contentId): self
    {
        if (!$this->contains($contentId)) {
            return $this;
        }

        $remaining = array_values(array_filter(
            $this->documents,
            static fn (SelectedDocument $document): bool => !$document->contentId()->equals($contentId),
        ));

        if ([] === $remaining) {
            throw new InvalidConversationDocumentException(
                'A conversation must contain at least one document.',
            );
        }

        return new self($remaining);
    }

    /**
     * @param list<SelectedDocument> $documents
     *
     * @return list<SelectedDocument>
     */
    private function deduplicatePreservingOrder(array $documents): array
    {
        $deduplicated = [];
        $seen = [];

        foreach ($documents as $document) {
            $value = $document->contentId()->value;

            if (isset($seen[$value])) {
                continue;
            }

            $seen[$value] = true;
            $deduplicated[] = $document;
        }

        return $deduplicated;
    }
}
