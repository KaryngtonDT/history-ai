<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Domain\Learning\LearningSignal;
use App\Domain\Learning\LearningSignalType;

final class ShadowLearningSignalMapper
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return list<LearningSignal>
     */
    public function map(array $payload): array
    {
        $event = is_string($payload['event'] ?? null) ? $payload['event'] : '';

        return match ($event) {
            'question_asked' => $this->mapQuestionAsked($payload),
            'intervention_shown' => [$this->mapInterventionShown($payload)],
            'intervention_skipped' => [$this->mapInterventionSkipped($payload)],
            'challenge_answered' => [$this->mapChallengeAnswered($payload)],
            'challenge_failed' => [$this->mapChallengeFailed($payload)],
            'repeated_vocabulary' => [$this->mapRepeatedVocabulary($payload)],
            'grammar_difficulty' => [$this->mapGrammarDifficulty($payload)],
            'translation_style_preference' => [$this->mapTranslationStylePreference($payload)],
            'explanation_depth_preference' => [$this->mapExplanationDepthPreference($payload)],
            'voice_language_preference' => [$this->mapVoiceLanguagePreference($payload)],
            'playback_pause_pattern' => [$this->mapPlaybackPausePattern($payload)],
            'topic_interest' => [$this->mapTopicInterest($payload)],
            default => [],
        };
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return list<LearningSignal>
     */
    private function mapQuestionAsked(array $payload): array
    {
        $questionType = is_string($payload['questionType'] ?? null) ? $payload['questionType'] : 'general';
        $term = is_string($payload['term'] ?? null) ? trim($payload['term']) : '';

        $signals = [
            LearningSignal::record(
                LearningSignalType::ShadowQuestionAsked,
                [
                    'summary' => sprintf('Shadow question asked (%s).', $questionType),
                    'questionType' => $questionType,
                    'term' => $term,
                ],
            ),
        ];

        if ('vocabulary' === $questionType && '' !== $term) {
            $signals[] = LearningSignal::record(
                LearningSignalType::RepeatedVocabulary,
                [
                    'summary' => sprintf('Repeated vocabulary interest: %s.', $term),
                    'term' => $term,
                ],
            );
        }

        return $signals;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapInterventionShown(array $payload): LearningSignal
    {
        $interventionType = is_string($payload['interventionType'] ?? null) ? $payload['interventionType'] : 'challenge';
        $difficulty = is_string($payload['difficulty'] ?? null) ? $payload['difficulty'] : 'medium';

        return LearningSignal::record(
            LearningSignalType::ShadowInterventionShown,
            [
                'summary' => sprintf('Shadow intervention shown (%s, %s).', $interventionType, $difficulty),
                'interventionType' => $interventionType,
                'difficulty' => $difficulty,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapInterventionSkipped(array $payload): LearningSignal
    {
        $difficulty = is_string($payload['difficulty'] ?? null) ? $payload['difficulty'] : 'hard';

        return LearningSignal::record(
            LearningSignalType::ShadowInterventionSkipped,
            [
                'summary' => sprintf('Shadow intervention skipped (%s).', $difficulty),
                'difficulty' => $difficulty,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapChallengeAnswered(array $payload): LearningSignal
    {
        $correct = (bool) ($payload['correct'] ?? false);
        $difficulty = is_string($payload['difficulty'] ?? null) ? $payload['difficulty'] : 'medium';

        return LearningSignal::record(
            LearningSignalType::ShadowChallengeAnswered,
            [
                'summary' => sprintf(
                    'Shadow challenge answered (%s, %s).',
                    $difficulty,
                    $correct ? 'correct' : 'incorrect',
                ),
                'correct' => $correct,
                'difficulty' => $difficulty,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapChallengeFailed(array $payload): LearningSignal
    {
        $difficulty = is_string($payload['difficulty'] ?? null) ? $payload['difficulty'] : 'hard';

        return LearningSignal::record(
            LearningSignalType::ShadowChallengeFailed,
            [
                'summary' => sprintf('Shadow challenge failed (%s).', $difficulty),
                'difficulty' => $difficulty,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapRepeatedVocabulary(array $payload): LearningSignal
    {
        $term = is_string($payload['term'] ?? null) ? trim($payload['term']) : 'unknown';

        return LearningSignal::record(
            LearningSignalType::RepeatedVocabulary,
            [
                'summary' => sprintf('Repeated vocabulary: %s.', $term),
                'term' => $term,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapGrammarDifficulty(array $payload): LearningSignal
    {
        $topic = is_string($payload['topic'] ?? null) ? trim($payload['topic']) : 'general';

        return LearningSignal::record(
            LearningSignalType::GrammarDifficulty,
            [
                'summary' => sprintf('Grammar difficulty observed: %s.', $topic),
                'topic' => $topic,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapTranslationStylePreference(array $payload): LearningSignal
    {
        $style = is_string($payload['style'] ?? null) ? $payload['style'] : 'natural';

        return LearningSignal::record(
            LearningSignalType::TranslationStylePreference,
            [
                'summary' => sprintf('Translation style preference: %s.', $style),
                'style' => $style,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapExplanationDepthPreference(array $payload): LearningSignal
    {
        $depth = is_string($payload['depth'] ?? null) ? $payload['depth'] : 'detailed';

        return LearningSignal::record(
            LearningSignalType::ExplanationDepthPreference,
            [
                'summary' => sprintf('Explanation depth preference: %s.', $depth),
                'depth' => $depth,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapVoiceLanguagePreference(array $payload): LearningSignal
    {
        $language = is_string($payload['language'] ?? null) ? strtolower(trim($payload['language'])) : 'en';

        return LearningSignal::record(
            LearningSignalType::VoiceLanguagePreference,
            [
                'summary' => sprintf('Voice language preference: %s.', $language),
                'language' => $language,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapPlaybackPausePattern(array $payload): LearningSignal
    {
        $segmentType = is_string($payload['segmentType'] ?? null) ? $payload['segmentType'] : 'technical';

        return LearningSignal::record(
            LearningSignalType::PlaybackPausePattern,
            [
                'summary' => sprintf('Playback pause in %s segment.', $segmentType),
                'segmentType' => $segmentType,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapTopicInterest(array $payload): LearningSignal
    {
        $topic = is_string($payload['topic'] ?? null) ? trim($payload['topic']) : 'general';

        return LearningSignal::record(
            LearningSignalType::TopicInterestObserved,
            [
                'summary' => sprintf('Topic interest observed: %s.', $topic),
                'topic' => $topic,
            ],
        );
    }
}
