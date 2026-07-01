<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatProviderOptions;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatSourceCollection;
use App\Application\Shadow\DTO\ShadowAnswerVoiceMetadata;
use App\Domain\Shadow\ShadowAnswer;
use App\Domain\Shadow\ShadowExplanationStyle;
use App\Domain\Shadow\ShadowQuestion;
use Throwable;

final class ShadowWatchAnswerer
{
    public const string FALLBACK_ANSWER = 'I could not answer right now. Please try again or rephrase your question.';

    public function __construct(
        private readonly ChatProviderInterface $chatProvider,
        private readonly ShadowWatchPromptBuilder $promptBuilder,
    ) {
    }

    public function answer(
        WatchContext $context,
        ShadowQuestion $question,
        ShadowAnswerVoiceMetadata $voice,
        ?ShadowExplanationStyle $explanationStyleHint = null,
    ): ShadowAnswer {
        try {
            $prompt = $this->promptBuilder->build($context, $question, $voice->answerLanguage, $explanationStyleHint);
            $response = $this->chatProvider->answer(ChatRequest::create(
                $prompt,
                ChatSourceCollection::empty(),
                ChatProviderOptions::defaults(),
            ));

            return ShadowAnswer::fromString($response->answer());
        } catch (Throwable) {
            return ShadowAnswer::fromString(self::FALLBACK_ANSWER);
        }
    }
}
