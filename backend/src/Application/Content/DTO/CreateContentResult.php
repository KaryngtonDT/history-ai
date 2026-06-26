<?php

declare(strict_types=1);

namespace App\Application\Content\DTO;

use App\Domain\Content\ContentId;

final readonly class CreateContentResult
{
    public function __construct(
        public ContentId $contentId,
    ) {
    }
}
