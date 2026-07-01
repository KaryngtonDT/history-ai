<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Shadow\ShadowChallenge;
use App\Domain\Shadow\ShadowChallengeLevel;
use App\Domain\Shadow\ShadowExplanationStyle;
use App\Domain\Shadow\ShadowInterventionType;

final class ShadowChallengeGenerator
{
    public function generate(
        ShadowInterventionType $type,
        ShadowChallengeLevel $level,
        ShadowExplanationStyle $style,
        ShadowInterventionContext $context,
        ?string $focusTerm = null,
    ): ?ShadowChallenge {
        $segmentText = $context->transcriptText() ?? 'this segment';

        return match ($type) {
            ShadowInterventionType::VocabularyCheck => ShadowChallenge::create(
                $this->vocabularyQuestion($level, $focusTerm ?? $this->extractFocusTerm($segmentText)),
                $this->vocabularyHint($style, $focusTerm),
            ),
            ShadowInterventionType::ConceptCheck => ShadowChallenge::create(
                match ($level) {
                    ShadowChallengeLevel::Easy => 'What is the main idea in one short sentence?',
                    ShadowChallengeLevel::Normal => 'Explain the main idea of this segment in your own words.',
                    ShadowChallengeLevel::Hard => 'Summarize this segment and connect it to what came before.',
                },
            ),
            ShadowInterventionType::GrammarCheck => ShadowChallenge::create(
                match ($level) {
                    ShadowChallengeLevel::Easy => 'Which phrase in this segment stands out grammatically?',
                    ShadowChallengeLevel::Normal => 'Identify one grammar pattern used in this segment.',
                    ShadowChallengeLevel::Hard => 'Explain how the sentence structure supports the speaker\'s point.',
                },
            ),
            ShadowInterventionType::ChallengeQuestion => ShadowChallenge::create(
                match ($level) {
                    ShadowChallengeLevel::Easy => 'What is one key takeaway from this moment?',
                    ShadowChallengeLevel::Normal => 'How would you explain this moment to a friend?',
                    ShadowChallengeLevel::Hard => 'What question would you ask the speaker about this moment?',
                },
            ),
            default => null,
        };
    }

    public function generateExplanation(
        ShadowInterventionType $type,
        ShadowExplanationStyle $style,
        ShadowInterventionContext $context,
    ): ?string {
        $translation = $context->translationText();
        $transcript = $context->transcriptText() ?? '';

        return match ($type) {
            ShadowInterventionType::Explanation => match ($style) {
                ShadowExplanationStyle::Short => sprintf(
                    'Briefly: "%s" is rendered as "%s".',
                    $this->truncate($transcript, 80),
                    $this->truncate($translation ?? 'an unclear translation', 80),
                ),
                ShadowExplanationStyle::Detailed => sprintf(
                    'The speaker says "%s". The translation is "%s". Listen for tone and emphasis.',
                    $this->truncate($transcript, 120),
                    $this->truncate($translation ?? 'unavailable', 120),
                ),
                ShadowExplanationStyle::ExampleFirst => sprintf(
                    'Example first: if someone said "%s", you might hear "%s" in %s.',
                    $this->truncate($transcript, 60),
                    $this->truncate($translation ?? 'a similar phrase', 60),
                    $context->watchContext->targetLanguage,
                ),
            },
            ShadowInterventionType::SummaryPrompt => match ($style) {
                ShadowExplanationStyle::Short => 'New topic starting — note the shift before continuing.',
                ShadowExplanationStyle::Detailed => 'The conversation moves to a new subject. Summarize the previous point in one line.',
                ShadowExplanationStyle::ExampleFirst => 'Think of the last topic as a chapter ending; what was its headline?',
            },
            ShadowInterventionType::ReflectionPrompt => match ($style) {
                ShadowExplanationStyle::Short => 'Pause and recall one fact from the last minute.',
                ShadowExplanationStyle::Detailed => 'Take a moment to reflect on what you understood so far.',
                ShadowExplanationStyle::ExampleFirst => 'Imagine explaining the last minute to someone — what would you say first?',
            },
            default => null,
        };
    }

    private function vocabularyQuestion(ShadowChallengeLevel $level, string $term): string
    {
        return match ($level) {
            ShadowChallengeLevel::Easy => sprintf('What does "%s" mean here?', $term),
            ShadowChallengeLevel::Normal => sprintf('How would you define "%s" in this context?', $term),
            ShadowChallengeLevel::Hard => sprintf('Explain "%s" and why it matters in this segment.', $term),
        };
    }

    private function vocabularyHint(ShadowExplanationStyle $style, ?string $term): ?string
    {
        if (null === $term) {
            return null;
        }

        return match ($style) {
            ShadowExplanationStyle::Short => 'Focus on the surrounding sentence.',
            ShadowExplanationStyle::Detailed => 'Consider how the speaker uses "'.$term.'" in context.',
            ShadowExplanationStyle::ExampleFirst => 'Try replacing "'.$term.'" with a simpler synonym.',
        };
    }

    private function extractFocusTerm(string $text): string
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];

        foreach ($words as $word) {
            $clean = trim($word, '.,;:!?"\'');
            if (strlen($clean) >= 10) {
                return $clean;
            }
        }

        return $words[0] ?? 'this term';
    }

    private function truncate(string $text, int $max): string
    {
        if (strlen($text) <= $max) {
            return $text;
        }

        return substr($text, 0, $max - 3).'...';
    }
}
