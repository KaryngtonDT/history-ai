<?php

declare(strict_types=1);

namespace App\Infrastructure\Chat;

use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatResponse;

final class MockChatProvider implements ChatProviderInterface
{
    public const string MOCK_ANSWER = 'Mock answer based on retrieved context.';

    public function answer(ChatRequest $request): ChatResponse
    {
        return new ChatResponse(self::MOCK_ANSWER, $request->sources());
    }
}
