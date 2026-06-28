<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatPrompt;
use App\Domain\Chat\ChatProviderOptions;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatSourceCollection;
use PHPUnit\Framework\TestCase;

final class ChatRequestTest extends TestCase
{
    public function testExposesPromptSourcesAndOptions(): void
    {
        $prompt = new ChatPrompt('Answer using the excerpts below.');
        $sources = ChatSourceCollection::empty();
        $options = new ChatProviderOptions(temperature: 0.5, maxTokens: 512);

        $request = new ChatRequest($prompt, $sources, $options);

        self::assertTrue($prompt->equals($request->prompt()));
        self::assertSame($sources, $request->sources());
        self::assertSame(0.5, $request->options()->temperature());
        self::assertSame(512, $request->options()->maxTokens());
    }

    public function testCreateUsesDefaultOptionsWhenOmitted(): void
    {
        $request = ChatRequest::create(
            new ChatPrompt('Answer using the excerpts below.'),
            ChatSourceCollection::empty(),
        );

        self::assertSame(
            ChatProviderOptions::DEFAULT_TEMPERATURE,
            $request->options()->temperature(),
        );
        self::assertSame(
            ChatProviderOptions::DEFAULT_MAX_TOKENS,
            $request->options()->maxTokens(),
        );
    }
}
