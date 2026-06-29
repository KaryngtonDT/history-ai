<?php

declare(strict_types=1);

namespace App\Application\Chat\Handlers;

use App\Application\Chat\Commands\AskConversationChatStreamCommand;
use App\Application\Chat\ContentChatStreamer;
use App\Application\Chat\DTO\ConversationChatStreamResult;
use App\Application\Platform\PlatformLoggerInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\ChatQuestion;
use App\Domain\Chat\ChatStream;
use App\Domain\Chat\ChatStreamEvent;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Chat\ConversationStream;
use App\Domain\Chat\ConversationStreamEvent;
use App\Domain\Chat\ConversationStreamEventCollection;
use App\Domain\Chat\Exception\ConversationContentMismatchException;
use App\Domain\Content\ContentId;

final class AskConversationChatStreamHandler
{
    private const string COMPONENT = 'AskConversationChatStreamHandler';

    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly ContentChatStreamer $contentChatStreamer,
        private readonly PlatformLoggerInterface $platformLogger,
    ) {
    }

    public function __invoke(AskConversationChatStreamCommand $command): ConversationChatStreamResult
    {
        $this->platformLogger->info(self::COMPONENT, 'request started', [
            'contentId' => $command->contentId,
            'conversationId' => $command->conversationId,
        ]);

        try {
            return $this->handle($command);
        } finally {
            $this->platformLogger->info(self::COMPONENT, 'request completed');
        }
    }

    private function handle(AskConversationChatStreamCommand $command): ConversationChatStreamResult
    {
        $contentId = new ContentId($command->contentId);
        $conversationId = new ConversationId($command->conversationId);
        $conversation = $this->resolveConversation($conversationId, $contentId);

        $conversation = $conversation->appendUser(
            new ChatMessage(ChatMessageRole::User, $command->question),
        );

        $chatStream = $this->contentChatStreamer->stream(
            $this->loadArtifactsInSelectedDocumentOrder($conversation),
            new ChatQuestion($command->question),
            self::COMPONENT,
        );

        $conversationStream = $this->toConversationStream($conversationId, $chatStream);
        $conversation = $conversation->appendAssistant(
            $conversationStream->toAssistantMessage(),
        );

        $this->conversationRepository->save($conversation);

        return ConversationChatStreamResult::fromDomain($conversationStream, $conversation);
    }

    private function resolveConversation(ConversationId $conversationId, ContentId $contentId): Conversation
    {
        $conversation = $this->conversationRepository->findById($conversationId);

        if (null === $conversation) {
            return Conversation::start($conversationId, $contentId);
        }

        if (!$conversation->containsDocument($contentId)) {
            throw new ConversationContentMismatchException(
                'Conversation does not belong to the requested content.',
            );
        }

        return $conversation;
    }

    /**
     * @return list<Artifact>
     */
    private function loadArtifactsInSelectedDocumentOrder(Conversation $conversation): array
    {
        $artifacts = [];

        foreach ($conversation->documents()->all() as $selectedDocument) {
            foreach ($this->artifactRepository->findByContentId($selectedDocument->contentId()) as $artifact) {
                $artifacts[] = $artifact;
            }
        }

        return $artifacts;
    }

    private function toConversationStream(ConversationId $conversationId, ChatStream $chatStream): ConversationStream
    {
        /** @var list<ConversationStreamEvent> $events */
        $events = array_map(
            static fn (ChatStreamEvent $event): ConversationStreamEvent => new ConversationStreamEvent(
                $event->index(),
                $event->token(),
            ),
            $chatStream->events()->events(),
        );

        return new ConversationStream($conversationId, new ConversationStreamEventCollection($events));
    }
}
