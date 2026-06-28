<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Chat;

use App\Application\Chat\DTO\ChatAnswerResult;
use App\Application\Chat\DTO\ChatCitationResult;
use App\Application\Chat\DTO\ChatSourceResult;

final class ChatAnswerResponse
{
    /**
     * @return array{
     *     answer: string,
     *     sources: list<array{
     *         artifactId: string,
     *         chunkId: string,
     *         text: string,
     *         score: float
     *     }>,
     *     citations: list<array{
     *         number: int,
     *         artifactId: string,
     *         chunkId: string,
     *         score: float
     *     }>
     * }
     */
    public static function fromResult(ChatAnswerResult $result): array
    {
        return [
            'answer' => $result->answer,
            'sources' => array_map(
                static fn (ChatSourceResult $source): array => [
                    'artifactId' => $source->artifactId,
                    'chunkId' => $source->chunkId,
                    'text' => $source->text,
                    'score' => $source->score,
                ],
                $result->sources,
            ),
            'citations' => array_map(
                static fn (ChatCitationResult $citation): array => [
                    'number' => $citation->number,
                    'artifactId' => $citation->artifactId,
                    'chunkId' => $citation->chunkId,
                    'score' => $citation->score,
                ],
                $result->citations,
            ),
        ];
    }
}
