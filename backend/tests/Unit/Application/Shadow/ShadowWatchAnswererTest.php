<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shadow;

use App\Application\Shadow\ShadowWatchAnswerer;
use App\Application\Shadow\ShadowWatchPromptBuilder;
use App\Application\Shadow\WatchContext;
use App\Application\Shadow\WatchContextSegment;
use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatResponse;
use App\Domain\Chat\ChatSourceCollection;
use App\Domain\Shadow\ShadowQuestion;
use App\Infrastructure\Chat\MockChatProvider;
use PHPUnit\Framework\TestCase;

final class ShadowWatchAnswererTest extends TestCase
{
    public function testBuildsPromptWithWatchContext(): void
    {
        $provider = new class implements ChatProviderInterface {
            public ?string $lastPrompt = null;

            public function answer(ChatRequest $request): ChatResponse
            {
                $this->lastPrompt = $request->prompt()->value();

                return new ChatResponse('Segment-aware answer.', ChatSourceCollection::empty());
            }
        };

        $answerer = new ShadowWatchAnswerer($provider, new ShadowWatchPromptBuilder());
        $context = new WatchContext(
            videoId: '550e8400-e29b-41d4-a716-446655440000',
            currentTimeSeconds: 7.5,
            targetLanguage: 'fr',
            conversationId: null,
            currentTranscriptSegment: new WatchContextSegment(1, 5.0, 10.0, 'Second segment.'),
            currentTranslationSegment: null,
            previousTranscriptSegment: null,
            nextTranscriptSegment: null,
            previousTranslationSegment: null,
            nextTranslationSegment: null,
            nearbyTranscriptContext: 'Hello world. Second segment.',
            nearbyTranslationContext: '',
            currentSpeaker: null,
            recentInteractions: [],
            conversationMemory: [],
        );

        $answer = $answerer->answer($context, ShadowQuestion::fromString('Explain this sentence.'));

        self::assertSame('Segment-aware answer.', $answer->text());
        self::assertStringContainsString('Second segment.', $provider->lastPrompt);
        self::assertStringContainsString('7.5 seconds', $provider->lastPrompt);
    }

    public function testUsesFallbackAnswerOnProviderFailure(): void
    {
        $provider = new class implements ChatProviderInterface {
            public function answer(ChatRequest $request): ChatResponse
            {
                throw new \RuntimeException('down');
            }
        };

        $answerer = new ShadowWatchAnswerer($provider, new ShadowWatchPromptBuilder());
        $context = new WatchContext(
            videoId: '550e8400-e29b-41d4-a716-446655440000',
            currentTimeSeconds: 1.0,
            targetLanguage: 'fr',
            conversationId: null,
            currentTranscriptSegment: null,
            currentTranslationSegment: null,
            previousTranscriptSegment: null,
            nextTranscriptSegment: null,
            previousTranslationSegment: null,
            nextTranslationSegment: null,
            nearbyTranscriptContext: '',
            nearbyTranslationContext: '',
            currentSpeaker: null,
            recentInteractions: [],
            conversationMemory: [],
        );

        $answer = $answerer->answer($context, ShadowQuestion::fromString('Translate more literally.'));

        self::assertSame(ShadowWatchAnswerer::FALLBACK_ANSWER, $answer->text());
    }
}
