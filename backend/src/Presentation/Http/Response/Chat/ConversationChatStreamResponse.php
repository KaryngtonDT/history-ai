<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Chat;

use App\Application\Chat\DTO\ConversationChatStreamResult;
use App\Application\Chat\DTO\ConversationStreamEventResult;
use Symfony\Component\HttpFoundation\Response;

final class ConversationChatStreamResponse
{
    public static function fromResult(ConversationChatStreamResult $result): Response
    {
        $body = '';

        foreach ($result->events as $event) {
            $body .= self::formatTokenEvent($event);
        }

        $body .= self::formatConversationEvent($result);
        $body .= "event: done\ndata: {}\n\n";

        return new Response(
            $body,
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ],
        );
    }

    private static function formatTokenEvent(ConversationStreamEventResult $event): string
    {
        return sprintf(
            "event: token\ndata: %s\n\n",
            json_encode(
                [
                    'index' => $event->index,
                    'text' => $event->text,
                ],
                JSON_THROW_ON_ERROR,
            ),
        );
    }

    private static function formatConversationEvent(ConversationChatStreamResult $result): string
    {
        return sprintf(
            "event: conversation\ndata: %s\n\n",
            json_encode(
                ConversationResponse::fromResult($result->conversation),
                JSON_THROW_ON_ERROR,
            ),
        );
    }
}
