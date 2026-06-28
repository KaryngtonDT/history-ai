<?php

declare(strict_types=1);

namespace App\Infrastructure\Chat;

use App\Domain\Chat\ChatCitation;
use App\Domain\Chat\ChatCitationCollection;
use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatResponse;

final class MockChatProvider implements ChatProviderInterface
{
    public const string MOCK_ANSWER = 'Mock answer based on retrieved context.';

    public function answer(ChatRequest $request): ChatResponse
    {
        $sources = $request->sources();

        if ($sources->isEmpty()) {
            return new ChatResponse(self::MOCK_ANSWER, $sources);
        }

        $citations = [];
        $markers = [];

        foreach ($sources->sources() as $index => $source) {
            $number = $index + 1;
            $citations[] = new ChatCitation($number, $source);
            $markers[] = sprintf('[%d]', $number);
        }

        $answer = rtrim(self::MOCK_ANSWER, '.').' '.implode('', $markers).'.';

        return new ChatResponse(
            $answer,
            $sources,
            new ChatCitationCollection($citations),
        );
    }
}
