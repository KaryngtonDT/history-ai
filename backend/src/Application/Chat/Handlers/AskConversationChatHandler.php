<?php

declare(strict_types=1);

namespace App\Application\Chat\Handlers;

use App\Application\Chat\Commands\AskContentChatCommand;
use App\Application\Chat\Commands\AskConversationChatCommand;
use App\Application\Chat\DTO\ConversationChatResult;
use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Chat\Exception\ConversationContentMismatchException;
use App\Domain\Content\ContentId;

final class AskConversationChatHandler
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
        private readonly AskContentChatHandler $askContentChatHandler,
    ) {
    }

    public function __invoke(AskConversationChatCommand $command): ConversationChatResult
    {
        $contentId = new ContentId($command->contentId);
        $conversationId = new ConversationId($command->conversationId);
        $conversation = $this->resolveConversation($conversationId, $contentId);

        $conversation = $conversation->appendUser(
            new ChatMessage(ChatMessageRole::User, $command->question),
        );

        $answer = ($this->askContentChatHandler)(new AskContentChatCommand(
            contentId: $command->contentId,
            question: $command->question,
        ));

        $conversation = $conversation->appendAssistant(
            new ChatMessage(ChatMessageRole::Assistant, $answer->answer),
        );

        $this->conversationRepository->save($conversation);

        return ConversationChatResult::fromDomain($conversation, $answer);
    }

    private function resolveConversation(ConversationId $conversationId, ContentId $contentId): Conversation
    {
        $conversation = $this->conversationRepository->findById($conversationId);

        if (null === $conversation) {
            return Conversation::start($conversationId, $contentId);
        }

        if (!$conversation->contentId()->equals($contentId)) {
            throw new ConversationContentMismatchException(
                'Conversation does not belong to the requested content.',
            );
        }

        return $conversation;
    }
}
