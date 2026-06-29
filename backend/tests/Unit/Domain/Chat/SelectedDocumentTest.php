<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\SelectedDocument;
use App\Domain\Content\ContentId;
use PHPUnit\Framework\TestCase;

final class SelectedDocumentTest extends TestCase
{
    public function testExposesContentId(): void
    {
        $contentId = new ContentId('550e8400-e29b-41d4-a716-446655440000');
        $document = new SelectedDocument($contentId);

        self::assertTrue($document->contentId()->equals($contentId));
    }
}
