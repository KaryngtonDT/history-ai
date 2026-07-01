<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Chat\ChatPrompt;
use App\Domain\Shadow\ShadowExplanationStyle;
use App\Domain\Shadow\ShadowVoiceLanguage;
use App\Domain\Shadow\ShadowQuestion;

final class ShadowWatchPromptBuilder
{
    public function build(
        WatchContext $context,
        ShadowQuestion $question,
        ShadowVoiceLanguage $answerLanguage,
        ?ShadowExplanationStyle $explanationStyleHint = null,
    ): ChatPrompt {
        $lines = [
            'You are Shadow, the Lumen AI watch companion. Answer using the current video moment.',
            sprintf('Respond in %s.', $answerLanguage->label()),
        ];

        if (null !== $explanationStyleHint) {
            $lines[] = match ($explanationStyleHint) {
                ShadowExplanationStyle::Short => 'Keep the answer concise and practical.',
                ShadowExplanationStyle::Detailed => 'Provide a detailed, step-by-step explanation.',
                ShadowExplanationStyle::ExampleFirst => 'Lead with a concrete example, then explain.',
            };
        }

        $lines[] = sprintf('Current playback time: %.1f seconds', $context->currentTimeSeconds);
        $lines[] = sprintf('Target language: %s', $context->targetLanguage);

        if (null !== $context->currentTranscriptSegment) {
            $segment = $context->currentTranscriptSegment;
            $lines[] = sprintf(
                'Current transcript segment [%d] (%.1f-%.1f s): %s',
                $segment->index,
                $segment->startTime,
                $segment->endTime,
                $segment->text,
            );
        }

        if (null !== $context->currentTranslationSegment?->translatedText) {
            $lines[] = sprintf(
                'Current translation: %s',
                $context->currentTranslationSegment->translatedText,
            );
        }

        if ('' !== $context->nearbyTranscriptContext) {
            $lines[] = 'Nearby transcript context: '.$context->nearbyTranscriptContext;
        }

        if ('' !== $context->nearbyTranslationContext) {
            $lines[] = 'Nearby translation context: '.$context->nearbyTranslationContext;
        }

        if ([] !== $context->conversationMemory) {
            $lines[] = 'Recent conversation memory: '.implode(' | ', $context->conversationMemory);
        }

        $lines[] = '';
        $lines[] = 'Viewer question:';
        $lines[] = $question->text();
        $lines[] = '';
        $lines[] = 'Cite the timestamp or segment when relevant.';

        return new ChatPrompt(implode("\n", $lines));
    }
}
