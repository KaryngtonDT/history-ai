<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Chat;

use App\Domain\Chat\ChatConversation;
use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\SelectedDocument;
use App\Domain\Chat\SelectedDocumentCollection;
use App\Domain\Content\ContentId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'conversation')]
#[ORM\Index(name: 'idx_conversation_content_id', columns: ['content_id'])]
class ConversationRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(name: 'content_id', type: Types::GUID)]
    private string $contentId;

    /**
     * @var list<array{contentId: string}>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $documents = [];

    /**
     * @var list<array{role: string, text: string}>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $messages = [];

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    private function __construct()
    {
    }

    public static function fromDomain(Conversation $conversation, DateTimeImmutable $now): self
    {
        $record = new self();
        $record->id = $conversation->id()->value;
        $record->contentId = $conversation->contentId()->value;
        $record->documents = self::documentsToJson($conversation);
        $record->messages = self::messagesToJson($conversation);
        $record->createdAt = $now;
        $record->updatedAt = $now;

        return $record;
    }

    public function updateFromDomain(Conversation $conversation, DateTimeImmutable $now): void
    {
        $this->contentId = $conversation->contentId()->value;
        $this->documents = self::documentsToJson($conversation);
        $this->messages = self::messagesToJson($conversation);
        $this->updatedAt = $now;
    }

    public function toDomain(): Conversation
    {
        return new Conversation(
            new ConversationId($this->id),
            self::documentsFromJson($this->documents, $this->contentId),
            new ChatConversation(self::messagesFromJson($this->messages)),
        );
    }

    /**
     * @return list<array{contentId: string}>
     */
    private static function documentsToJson(Conversation $conversation): array
    {
        return array_map(
            static fn (SelectedDocument $document): array => [
                'contentId' => $document->contentId()->value,
            ],
            $conversation->documents()->all(),
        );
    }

    /**
     * @param list<array{contentId: string}> $documents
     */
    private static function documentsFromJson(array $documents, string $fallbackContentId): SelectedDocumentCollection
    {
        if ([] === $documents) {
            return SelectedDocumentCollection::fromContentId(new ContentId($fallbackContentId));
        }

        return new SelectedDocumentCollection(
            array_map(
                static fn (array $document): SelectedDocument => new SelectedDocument(
                    new ContentId($document['contentId']),
                ),
                $documents,
            ),
        );
    }

    /**
     * @return list<array{role: string, text: string}>
     */
    private static function messagesToJson(Conversation $conversation): array
    {
        return array_map(
            static fn (ChatMessage $message): array => [
                'role' => $message->role()->value,
                'text' => $message->content(),
            ],
            $conversation->messages(),
        );
    }

    /**
     * @param list<array{role: string, text: string}> $messages
     *
     * @return list<ChatMessage>
     */
    private static function messagesFromJson(array $messages): array
    {
        return array_map(
            static fn (array $message): ChatMessage => new ChatMessage(
                ChatMessageRole::from($message['role']),
                $message['text'],
            ),
            $messages,
        );
    }
}
