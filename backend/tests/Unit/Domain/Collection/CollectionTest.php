<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Collection;

use App\Domain\Collection\Collection;
use App\Domain\Collection\CollectionDescription;
use App\Domain\Collection\CollectionId;
use App\Domain\Collection\CollectionName;
use App\Domain\Collection\Exception\InvalidCollectionException;
use App\Domain\Collection\Exception\InvalidCollectionNameException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CollectionTest extends TestCase
{
    public function testCreateValidCollection(): void
    {
        $id = CollectionId::generate();
        $name = new CollectionName('Ancient Rome');
        $description = new CollectionDescription('Roman history and culture.');

        $collection = Collection::create($id, $name, $description);

        self::assertTrue($collection->id()->equals($id));
        self::assertTrue($collection->name()->equals($name));
        self::assertTrue($collection->description()->equals($description));
        self::assertSame('Ancient Rome', $collection->name()->value);
        self::assertSame('Roman history and culture.', $collection->description()->value);
        self::assertLessThanOrEqual(new \DateTimeImmutable(), $collection->createdAt());
    }

    public function testCreateCollectionWithEmptyDescription(): void
    {
        $collection = Collection::create(
            CollectionId::generate(),
            new CollectionName('Philosophy'),
            new CollectionDescription(''),
        );

        self::assertTrue($collection->description()->isEmpty());
    }

    public function testAggregateIsImmutable(): void
    {
        $reflection = new ReflectionClass(Collection::class);

        self::assertTrue($reflection->isFinal());

        foreach ($reflection->getProperties() as $property) {
            self::assertTrue(
                $property->isReadOnly(),
                sprintf('Property %s must be readonly.', $property->getName()),
            );
            self::assertTrue(
                $property->isPrivate(),
                sprintf('Property %s must be private.', $property->getName()),
            );
        }
    }

    public function testAggregateHasNoMutators(): void
    {
        $reflection = new ReflectionClass(Collection::class);
        $allowedMethods = [
            'create',
            'reconstitute',
            'id',
            'name',
            'description',
            'createdAt',
        ];

        $mutators = array_filter(
            $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
            static fn (\ReflectionMethod $method): bool => !in_array($method->getName(), $allowedMethods, true)
                && !str_starts_with($method->getName(), '__'),
        );

        self::assertSame([], array_map(
            static fn (\ReflectionMethod $method): string => $method->getName(),
            $mutators,
        ));
    }

    #[DataProvider('invalidNameProvider')]
    public function testInvalidNameIsRejected(string $name): void
    {
        $this->expectException(InvalidCollectionNameException::class);
        $this->expectExceptionMessage('Collection name cannot be empty.');

        new CollectionName($name);
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

    public function testInvalidCollectionIdIsRejected(): void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->expectExceptionMessage('Collection id must be a valid UUID.');

        new CollectionId('not-a-uuid');
    }

    public function testReconstitutePreservesPersistedState(): void
    {
        $createdAt = new \DateTimeImmutable('2026-06-27 12:00:00');
        $id = CollectionId::generate();
        $name = new CollectionName('Languages');
        $description = new CollectionDescription('Language learning resources.');

        $collection = Collection::reconstitute(
            $id,
            $name,
            $description,
            $createdAt,
        );

        self::assertSame($createdAt, $collection->createdAt());
        self::assertSame('Languages', $collection->name()->value);
        self::assertSame('Language learning resources.', $collection->description()->value);
    }
}
