<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatModel;
use App\Domain\Chat\ChatPrompt;
use App\Domain\Chat\ChatProviderOptions;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatResponse;
use App\Domain\Chat\ChatSourceCollection;
use App\Domain\Chat\Exception\InvalidChatQuestionException;
use PHPUnit\Framework\TestCase;

final class ChatProviderOptionsTest extends TestCase
{
    public function testDefaultsExposeExpectedValues(): void
    {
        $options = ChatProviderOptions::defaults();

        self::assertSame(0.2, $options->temperature());
        self::assertSame(1024, $options->maxTokens());
        self::assertNull($options->model());
    }

    public function testAcceptsOptionalModel(): void
    {
        $model = new ChatModel('gemini-2.0-flash');
        $options = new ChatProviderOptions(model: $model);

        self::assertTrue($model->equals($options->model()));
    }

    public function testRejectsInvalidTemperature(): void
    {
        $this->expectException(InvalidChatQuestionException::class);

        new ChatProviderOptions(temperature: 2.1);
    }

    public function testRejectsInvalidMaxTokens(): void
    {
        $this->expectException(InvalidChatQuestionException::class);

        new ChatProviderOptions(maxTokens: 0);
    }
}
