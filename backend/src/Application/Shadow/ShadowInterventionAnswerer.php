<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Application\Shadow\DTO\ShadowAnswerVoiceMetadata;
use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatProviderOptions;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatSourceCollection;
use App\Domain\Shadow\ShadowChallengeAnswer;
use App\Domain\Shadow\ShadowIntervention;
use Throwable;

final class ShadowInterventionAnswerer
{
    public const string FALLBACK_REPLY = 'Thanks for your answer. You can continue watching, ask for more detail, or say skip.';

    public function __construct(
        private readonly ChatProviderInterface $chatProvider,
        private readonly ShadowInterventionAnswerPromptBuilder $promptBuilder,
    ) {
    }

    public function reply(
        WatchContext $context,
        ShadowIntervention $intervention,
        ShadowChallengeAnswer $answer,
        ShadowAnswerVoiceMetadata $voice,
    ): string {
        if ($this->isContinueCommand($answer->text())) {
            return 'Continuing playback. Say "explain more" anytime if you want another hint.';
        }

        if ($this->isSkipCommand($answer->text())) {
            return 'Skipped. Shadow will stay quieter for a while.';
        }

        try {
            $prompt = $this->promptBuilder->build($context, $intervention, $answer, $voice->answerLanguage);
            $response = $this->chatProvider->answer(ChatRequest::create(
                $prompt,
                ChatSourceCollection::empty(),
                ChatProviderOptions::defaults(),
            ));

            $text = trim($response->answer());

            return '' !== $text ? $text : self::FALLBACK_REPLY;
        } catch (Throwable) {
            return $this->deterministicFallback($intervention, $answer);
        }
    }

    private function deterministicFallback(
        ShadowIntervention $intervention,
        ShadowChallengeAnswer $answer,
    ): string {
        $suggested = $intervention->challenge()?->suggestedAnswer()
            ?? $intervention->suggestedAnswer();

        if (null !== $suggested && $this->answersOverlap($answer->text(), $suggested)) {
            return 'Good answer. That matches the key idea. You can continue or ask for more detail.';
        }

        if (strlen(trim($answer->text())) >= 12) {
            return 'Thanks for explaining in your own words. You can continue or ask Shadow to explain more.';
        }

        return self::FALLBACK_REPLY;
    }

    private function isContinueCommand(string $text): bool
    {
        return 1 === preg_match('/^\s*(continue|resume|go on)\s*$/i', $text);
    }

    private function isSkipCommand(string $text): bool
    {
        return 1 === preg_match('/^\s*(skip|don\'?t ask again)\s*$/i', $text);
    }

    private function answersOverlap(string $userAnswer, string $suggested): bool
    {
        $user = strtolower(trim($userAnswer));
        $needle = strtolower(trim($suggested));

        return str_contains($user, $needle) || str_contains($needle, $user);
    }
}
