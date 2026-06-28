<?php

declare(strict_types=1);

namespace App\Domain\Chat;

interface ChatProviderInterface
{
    public function answer(ChatPrompt $prompt, ChatSourceCollection $sources): ChatAnswer;
}
