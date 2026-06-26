<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Content;

use App\Domain\Content\ContentId;

final readonly class CreateContentResponse
{
    public function __construct(
        public string $id,
    ) {
    }

    public static function fromContentId(ContentId $contentId): self
    {
        return new self($contentId->value);
    }

    /**
     * @return array{id: string}
     */
    public function toArray(): array
    {
        return ['id' => $this->id];
    }
}
