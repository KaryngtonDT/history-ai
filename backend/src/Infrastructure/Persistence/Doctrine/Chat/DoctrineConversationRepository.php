<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Chat;

use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationCollection;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Content\ContentId;
use DateTimeImmutable;
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
        /** @var list<ConversationRecord> $records */
        $records = $this->entityManager->getRepository(ConversationRecord::class)->findBy(
            ['contentId' => $contentId->value],
            ['updatedAt' => 'DESC'],
        );

        return new ConversationCollection(
            array_map(
                static fn (ConversationRecord $record): Conversation => $record->toDomain(),
                $records,
            ),
        );
    }
}
