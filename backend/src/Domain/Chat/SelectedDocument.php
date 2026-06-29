<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Content\ContentId;

final readonly class SelectedDocument
{
    public function __construct(
        private ContentId $contentId,
    ) {
    }

    public function contentId(): ContentId
    {
        return $this->contentId;
    }
}
