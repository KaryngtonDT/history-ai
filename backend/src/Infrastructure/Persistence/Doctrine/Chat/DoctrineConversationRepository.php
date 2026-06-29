<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Chat;

use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationCollection;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Content\ContentId;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineConversationRepository implements ConversationRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Conversation $conversation): void
    {
        $repository = $this->entityManager->getRepository(ConversationRecord::class);
        $record = $repository->find($conversation->id()->value);
        $now = new DateTimeImmutable();

        if (null === $record) {
            $this->entityManager->persist(ConversationRecord::fromDomain($conversation, $now));
        } else {
            $record->updateFromDomain($conversation, $now);
        }

        $this->entityManager->flush();
    }

    public function findById(ConversationId $conversationId): ?Conversation
    {
        $record = $this->entityManager->find(ConversationRecord::class, $conversationId->value);

        return $record?->toDomain();
    }

    public function findByContentId(ContentId $contentId): ConversationCollection
    {
        $connection = $this->entityManager->getConnection();
        $ids = $this->findRecordIdsByContentId($connection, $contentId->value);

        $conversations = [];

        foreach ($ids as $id) {
            $record = $this->entityManager->find(ConversationRecord::class, $id);

            if (null !== $record) {
                $conversations[] = $record->toDomain();
            }
        }

        return new ConversationCollection($conversations);
    }

    /**
     * @return list<string>
     */
    private function findRecordIdsByContentId(Connection $connection, string $contentId): array
    {
        $platform = $connection->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            /** @var list<string> */
            return $connection->fetchFirstColumn(
                <<<'SQL'
                    SELECT id
                    FROM conversation
                    WHERE content_id = :contentId
                       OR EXISTS (
                            SELECT 1
                            FROM json_array_elements(documents) AS elem
                            WHERE elem->>'contentId' = :contentId
                        )
                    ORDER BY updated_at DESC
                    SQL,
                ['contentId' => $contentId],
            );
        }

        if ($platform instanceof SqlitePlatform) {
            /** @var list<string> */
            return $connection->fetchFirstColumn(
                <<<'SQL'
                    SELECT id
                    FROM conversation
                    WHERE content_id = :contentId
                       OR EXISTS (
                            SELECT 1
                            FROM json_each(documents)
                            WHERE json_extract(value, '$.contentId') = :contentId
                        )
                    ORDER BY updated_at DESC
                    SQL,
                ['contentId' => $contentId],
            );
        }

        /** @var list<ConversationRecord> $records */
        $records = $this->entityManager->getRepository(ConversationRecord::class)->findBy(
            ['contentId' => $contentId],
            ['updatedAt' => 'DESC'],
        );

        return array_map(
            static fn (ConversationRecord $record): string => $record->toDomain()->id()->value,
            $records,
        );
    }
}
