<?php

declare(strict_types=1);

namespace App\Infrastructure\Chat;

use App\Domain\Chat\ChatCitation;
use App\Domain\Chat\ChatCitationCollection;
use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatResponse;
use App\Domain\Chat\ChatStream;
use App\Domain\Chat\ChatStreamEvent;
use App\Domain\Chat\ChatStreamEventCollection;
use App\Domain\Chat\ChatToken;
use App\Domain\Chat\StreamingChatProviderInterface;

final class MockChatProvider implements ChatProviderInterface, StreamingChatProviderInterface
{
    public const string MOCK_ANSWER = 'Mock answer based on retrieved context.';

    /** @var list<string> */
    private const array BASE_TOKENS = [
        'Mock ',
        'answer ',
        'based ',
        'on ',
        'retrieved ',
        'context ',
    ];

    public function answer(ChatRequest $request): ChatResponse
    {
        $sources = $request->sources();
        $answer = $this->buildAnswerText($request);

        if ($sources->isEmpty()) {
            return new ChatResponse($answer, $sources);
        }

        $citations = [];

        foreach ($sources->sources() as $index => $source) {
            $citations[] = new ChatCitation($index + 1, $source);
        }

        return new ChatResponse(
            $answer,
            $sources,
            new ChatCitationCollection($citations),
        );
    }

    public function stream(ChatRequest $request): ChatStream
    {
        $tokens = self::BASE_TOKENS;
        $sources = $request->sources();

        if ($sources->isEmpty()) {
            $tokens[] = '.';
        } else {
            $markers = [];

            foreach ($sources->sources() as $index => $source) {
                $markers[] = sprintf('[%d]', $index + 1);
            }

            $lastMarker = array_pop($markers);

            foreach ($markers as $marker) {
                $tokens[] = $marker;
            }

            $tokens[] = $lastMarker.'.';
        }

        $events = [];

        foreach ($tokens as $index => $tokenText) {
            $events[] = new ChatStreamEvent($index, new ChatToken($tokenText));
        }

        return new ChatStream(new ChatStreamEventCollection($events));
    }

    private function buildAnswerText(ChatRequest $request): string
    {
        $sources = $request->sources();

        if ($sources->isEmpty()) {
            return self::MOCK_ANSWER;
        }

        $markers = [];

        foreach ($sources->sources() as $index => $source) {
            $markers[] = sprintf('[%d]', $index + 1);
        }

        return rtrim(self::MOCK_ANSWER, '.').' '.implode('', $markers).'.';
    }
}
