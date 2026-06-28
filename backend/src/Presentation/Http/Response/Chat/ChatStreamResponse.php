<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Chat;

use App\Application\Chat\DTO\ChatStreamEventResult;
use App\Application\Chat\DTO\ChatStreamResult;
use Symfony\Component\HttpFoundation\Response;

final class ChatStreamResponse
{
    public static function fromResult(ChatStreamResult $result): Response
    {
        $body = '';

        foreach ($result->events as $event) {
            $body .= self::formatTokenEvent($event);
        }

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

    private static function formatTokenEvent(ChatStreamEventResult $event): string
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
}
