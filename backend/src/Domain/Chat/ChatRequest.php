<?php

declare(strict_types=1);

namespace App\Domain\Chat;

final readonly class ChatRequest
{
    public function __construct(
        private ChatPrompt $prompt,
        private ChatSourceCollection $sources,
        private ChatProviderOptions $options,
    ) {
    }

    public static function create(
        ChatPrompt $prompt,
        ChatSourceCollection $sources,
        ?ChatProviderOptions $options = null,
    ): self {
        return new self(
            $prompt,
            $sources,
            $options ?? ChatProviderOptions::defaults(),
        );
    }

    public function prompt(): ChatPrompt
    {
        return $this->prompt;
    }

    public function sources(): ChatSourceCollection
    {
        return $this->sources;
    }

    public function options(): ChatProviderOptions
    {
        return $this->options;
    }
}
