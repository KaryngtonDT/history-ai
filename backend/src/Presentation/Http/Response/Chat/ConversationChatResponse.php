<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Chat;

use App\Application\Chat\DTO\ConversationChatResult;
use App\Application\Chat\DTO\ConversationMessageResult;

final class ConversationChatResponse
{
    /**
     * @return array{
     *     conversation: array{
     *         id: string,
     *         contentId: string,
     *         messages: list<array{role: string, text: string}>
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
            'conversation' => [
                'id' => $result->conversation->id,
                'contentId' => $result->conversation->contentId,
                'messages' => array_map(
                    static fn (ConversationMessageResult $message): array => [
                        'role' => $message->role,
                        'text' => $message->text,
                    ],
                    $result->conversation->messages,
                ),
            ],
            'answer' => ChatAnswerResponse::fromResult($result->answer),
        ];
    }
}
