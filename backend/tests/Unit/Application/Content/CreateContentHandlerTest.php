<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Content;

use App\Application\Content\Commands\CreateContentCommand;
use App\Application\Content\Handlers\CreateContentHandler;
use App\Domain\Content\Content;
use App\Domain\Content\ContentRepositoryInterface;
use App\Domain\Content\ContentSourceType;
use App\Domain\Content\ContentStatus;
use App\Domain\Content\Exception\InvalidContentTitleException;
use PHPUnit\Framework\TestCase;

final class CreateContentHandlerTest extends TestCase
{
    public function testCreatesContentAndReturnsContentId(): void
    {
        $repository = $this->createMock(ContentRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Content $content): bool {
                return 'Roman Empire Overview' === $content->title()->value
                    && ContentSourceType::UploadPdf === $content->sourceType()
                    && ContentStatus::Draft === $content->status();
            }));

        $handler = new CreateContentHandler($repository);

        $result = $handler(new CreateContentCommand(
            title: 'Roman Empire Overview',
            sourceType: ContentSourceType::UploadPdf,
        ));

        self::assertNotEmpty($result->contentId->value);
    }

    public function testEmptyTitleIsRejected(): void
    {
        $repository = $this->createMock(ContentRepositoryInterface::class);
        $repository->expects(self::never())->method('save');

        $handler = new CreateContentHandler($repository);

        $this->expectException(InvalidContentTitleException::class);

        $handler(new CreateContentCommand(
            title: '   ',
            sourceType: ContentSourceType::UploadAudio,
        ));
    }

    public function testRepositorySaveIsCalledExactlyOnce(): void
    {
        $repository = $this->createMock(ContentRepositoryInterface::class);
        $repository->expects(self::once())->method('save');

        $handler = new CreateContentHandler($repository);

        $handler(new CreateContentCommand(
            title: 'Valid Title',
            sourceType: ContentSourceType::YoutubeUrl,
        ));
    }
}
