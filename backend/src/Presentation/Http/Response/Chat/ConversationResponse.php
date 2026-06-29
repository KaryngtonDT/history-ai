<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Chat;

use App\Application\Chat\DTO\ConversationMessageResult;
use App\Application\Chat\DTO\ConversationResult;
use App\Application\Chat\DTO\SelectedDocumentResult;

final class ConversationResponse
{
    /**
     * @return array{
     *     conversation: array{
     *         id: string,
     *         contentId: string,
     *         messages: list<array{role: string, text: string}>,
     *         documents: list<array{contentId: string}>
     *     }
     * }
     */
    public static function fromResult(ConversationResult $result): array
    {
        return [
            'conversation' => self::conversationToArray($result),
        ];
    }

    /**
     * @return array{
     *     id: string,
     *     contentId: string,
     *     messages: list<array{role: string, text: string}>,
     *     documents: list<array{contentId: string}>
     * }
     */
    public static function conversationToArray(ConversationResult $result): array
    {
        return [
            'id' => $result->id,
            'contentId' => $result->contentId,
            'messages' => array_map(
                static fn (ConversationMessageResult $message): array => [
                    'role' => $message->role,
                    'text' => $message->text,
                ],
                $result->messages,
            ),
            'documents' => array_map(
                static fn (SelectedDocumentResult $document): array => [
                    'contentId' => $document->contentId,
                ],
                $result->documents,
            ),
        ];
    }
}
