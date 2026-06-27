<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Collection;

use App\Application\Collection\Commands\CreateCollectionCommand;
use App\Application\Collection\Handlers\CreateCollectionHandler;
use App\Domain\Collection\Collection;
use App\Domain\Collection\CollectionRepositoryInterface;
use App\Domain\Collection\Exception\InvalidCollectionNameException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CreateCollectionHandlerTest extends TestCase
{
    public function testValidCommandCreatesCollection(): void
    {
        $repository = $this->createMock(CollectionRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Collection $collection): bool {
                return 'Ancient Rome' === $collection->name()->value
                    && 'Roman history and culture.' === $collection->description()->value;
            }));

        $handler = new CreateCollectionHandler($repository);

        $result = $handler(new CreateCollectionCommand(
            name: 'Ancient Rome',
            description: 'Roman history and culture.',
        ));

        self::assertNotEmpty($result->collectionId->value);
        self::assertSame('Ancient Rome', $result->name->value);
        self::assertSame('Roman history and culture.', $result->description->value);
        self::assertInstanceOf(\DateTimeImmutable::class, $result->createdAt);
    }

    public function testRepositorySaveIsCalledExactlyOnce(): void
    {
        $repository = $this->createMock(CollectionRepositoryInterface::class);
        $repository->expects(self::once())->method('save');

        $handler = new CreateCollectionHandler($repository);

        $handler(new CreateCollectionCommand(
            name: 'Philosophy',
            description: 'Philosophy resources.',
        ));
    }

    #[DataProvider('invalidNameProvider')]
    public function testInvalidNameIsRejectedByDomain(string $name): void
    {
        $repository = $this->createMock(CollectionRepositoryInterface::class);
        $repository->expects(self::never())->method('save');

        $handler = new CreateCollectionHandler($repository);

        $this->expectException(InvalidCollectionNameException::class);

        $handler(new CreateCollectionCommand(
            name: $name,
            description: 'Valid description.',
        ));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidNameProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'whitespace only' => ['   '];
        yield 'tab and newline' => ["\t\n"];
    }

    public function testEmptyDescriptionIsAccepted(): void
    {
        $repository = $this->createMock(CollectionRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Collection $collection): bool {
                return $collection->description()->isEmpty();
            }));

        $handler = new CreateCollectionHandler($repository);

        $result = $handler(new CreateCollectionCommand(
            name: 'Languages',
            description: '',
        ));

        self::assertTrue($result->description->isEmpty());
        self::assertSame('Languages', $result->name->value);
    }
}
