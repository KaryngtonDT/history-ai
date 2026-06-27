<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Search;

use App\Domain\Library\LibraryItem;
use App\Domain\Search\LibrarySearchRepositoryInterface;
use App\Domain\Search\SearchQuery;
use App\Infrastructure\Persistence\Doctrine\Library\LibraryItemRecord;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineLibrarySearchRepository implements LibrarySearchRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function search(SearchQuery $query): array
    {
        $pattern = '%'.$this->escapeLikePattern($query->value()).'%';

        /** @var list<LibraryItemRecord> $records */
        $records = $this->entityManager->getRepository(LibraryItemRecord::class)
            ->createQueryBuilder('library_item')
            ->where('LOWER(library_item.title) LIKE LOWER(:query)')
            ->setParameter('query', $pattern)
            ->orderBy('library_item.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (LibraryItemRecord $record): LibraryItem => $record->toDomain(),
            $records,
        );
    }

    private function escapeLikePattern(string $value): string
    {
        return addcslashes($value, '\\%_');
    }
}
