<?php

declare(strict_types=1);

namespace App\Infrastructure\Chat;

use App\Domain\Chat\ChatAnswer;
use App\Domain\Chat\ChatPrompt;
use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatSourceCollection;

final class MockChatProvider implements ChatProviderInterface
{
    public const string MOCK_ANSWER = 'Mock answer based on retrieved context.';

    public function answer(ChatPrompt $prompt, ChatSourceCollection $sources): ChatAnswer
    {
        return new ChatAnswer(self::MOCK_ANSWER, $sources);
    }
}
