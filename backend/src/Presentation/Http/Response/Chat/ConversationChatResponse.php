<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Chat;

use App\Application\Chat\DTO\ConversationChatResult;

final class ConversationChatResponse
{
    /**
     * @return array{
     *     conversation: array{
     *         id: string,
     *         contentId: string,
     *         messages: list<array{role: string, text: string}>,
     *         documents: list<array{contentId: string}>
     *     },
     *     answer: array{
     *         answer: string,
     *         sources: list<array{
     *             artifactId: string,
     *             chunkId: string,
     *             text: string,
     *             score: float
     *         }>,
     *         citations: list<array{
     *             number: int,
     *             artifactId: string,
     *             chunkId: string,
     *             score: float
     *         }>
     *     }
     * }
     */
    public static function fromResult(ConversationChatResult $result): array
    {
        return [
            'conversation' => ConversationResponse::conversationToArray($result->conversation),
            'answer' => ChatAnswerResponse::fromResult($result->answer),
        ];
    }
}
