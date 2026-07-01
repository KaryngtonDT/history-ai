<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Shadow\ShadowIntervention;
use App\Domain\Shadow\ShadowInterventionId;
use App\Domain\Shadow\ShadowInterventionTrigger;
use App\Domain\Shadow\ShadowInterventionType;
use App\Domain\Shadow\ShadowInteractionKind;
use App\Domain\Shadow\ShadowTimestamp;

final class ShadowInterventionDecider
{
    public function __construct(
        private readonly ShadowInterventionReasonBuilder $reasonBuilder,
        private readonly ShadowChallengeGenerator $challengeGenerator,
    ) {
    }

    public function decide(ShadowInterventionContext $context): ?ShadowIntervention
    {
        if (!$context->policy->enabled() || $context->isPaused()) {
            return null;
        }

        $candidate = $this->detectCandidate($context);

        if (null === $candidate) {
            return null;
        }

        return $this->buildIntervention($context, $candidate['type'], $candidate['trigger'], $candidate['detail'] ?? null);
    }

    /**
     * @return array{type: ShadowInterventionType, trigger: ShadowInterventionTrigger, detail?: string}|null
     */
    private function detectCandidate(ShadowInterventionContext $context): ?array
    {
        $vocabulary = $this->detectDifficultVocabulary($context->transcriptText() ?? '');

        if (null !== $vocabulary) {
            return [
                'type' => ShadowInterventionType::VocabularyCheck,
                'trigger' => ShadowInterventionTrigger::UnknownVocabulary,
                'detail' => $vocabulary,
            ];
        }

        if ($this->hasLowConfidenceTranslation($context)) {
            return [
                'type' => ShadowInterventionType::Explanation,
                'trigger' => ShadowInterventionTrigger::LowConfidenceTranslation,
            ];
        }

        if ($this->hasTopicShift($context)) {
            return [
                'type' => ShadowInterventionType::SummaryPrompt,
                'trigger' => ShadowInterventionTrigger::TopicShift,
            ];
        }

        if ($this->hasRepeatedPausesNearby($context)) {
            return [
                'type' => ShadowInterventionType::ConceptCheck,
                'trigger' => ShadowInterventionTrigger::RepeatedConcept,
            ];
        }

        if ($this->hasUserConfusion($context)) {
            return [
                'type' => ShadowInterventionType::ConceptCheck,
                'trigger' => ShadowInterventionTrigger::UserConfusion,
            ];
        }

        if ($this->hasLongSilence($context)) {
            return [
                'type' => ShadowInterventionType::ReflectionPrompt,
                'trigger' => ShadowInterventionTrigger::LongSilence,
            ];
        }

        if ($this->isImportantSegment($context)) {
            return [
                'type' => ShadowInterventionType::Explanation,
                'trigger' => ShadowInterventionTrigger::ImportantSegment,
            ];
        }

        return null;
    }

    private function buildIntervention(
        ShadowInterventionContext $context,
        ShadowInterventionType $type,
        ShadowInterventionTrigger $trigger,
        ?string $detail,
    ): ShadowIntervention {
        $policy = $context->policy;
        $reason = $this->reasonBuilder->build($trigger, $context, $detail);
        $challenge = $this->challengeGenerator->generate(
            $type,
            $policy->challengeLevel(),
            $policy->explanationStyle(),
            $context,
            $detail,
        );
        $explanation = $this->challengeGenerator->generateExplanation(
            $type,
            $policy->explanationStyle(),
            $context,
        );

        return ShadowIntervention::create(
            ShadowInterventionId::generate(),
            $type,
            $trigger,
            $reason,
            ShadowTimestamp::fromSeconds($context->currentTimeSeconds()),
            $this->expectedUserAction($type),
            $policy->allowAutoPause(),
            $challenge,
            $explanation,
        );
    }

    private function expectedUserAction(ShadowInterventionType $type): string
    {
        return match ($type) {
            ShadowInterventionType::Explanation => 'Confirm understanding or ask for more detail',
            ShadowInterventionType::VocabularyCheck,
            ShadowInterventionType::GrammarCheck,
            ShadowInterventionType::ConceptCheck,
            ShadowInterventionType::ChallengeQuestion => 'Answer the challenge or say skip',
            ShadowInterventionType::SummaryPrompt => 'Summarize the topic shift briefly',
            ShadowInterventionType::ReflectionPrompt => 'Reflect on what you understood',
        };
    }

    private function detectDifficultVocabulary(string $text): ?string
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];

        foreach ($words as $word) {
            $clean = trim($word, '.,;:!?"\'');
            if ('' === $clean) {
                continue;
            }

            if (strlen($clean) >= 12 || 1 === preg_match('/^[A-Z]{2,}$/', $clean)) {
                return $clean;
            }
        }

        return null;
    }

    private function hasLowConfidenceTranslation(ShadowInterventionContext $context): bool
    {
        $transcript = $context->transcriptText();
        $translation = $context->translationText();

        if (null === $transcript || '' === trim($transcript)) {
            return false;
        }

        if (null === $translation || '' === trim($translation)) {
            return true;
        }

        return strcasecmp(trim($transcript), trim($translation)) === 0
            && strcasecmp($context->watchContext->targetLanguage, 'en') !== 0;
    }

    private function hasTopicShift(ShadowInterventionContext $context): bool
    {
        $current = $context->transcriptText();
        $previous = $context->previousTranscriptText();

        if (null === $current || null === $previous) {
            return false;
        }

        $currentLead = $this->leadWord($current);
        $previousLead = $this->leadWord($previous);

        return '' !== $currentLead
            && '' !== $previousLead
            && strcasecmp($currentLead, $previousLead) !== 0;
    }

    private function hasRepeatedPausesNearby(ShadowInterventionContext $context): bool
    {
        return $this->countInteractionsNear(
            $context,
            ShadowInteractionKind::Pause,
            90.0,
        ) >= 2;
    }

    private function hasUserConfusion(ShadowInterventionContext $context): bool
    {
        $pauses = $this->countInteractionsNear($context, ShadowInteractionKind::Pause, 60.0);
        $questions = $this->countInteractionsNear($context, ShadowInteractionKind::Question, 60.0);

        return $pauses >= 1 && $questions >= 1;
    }

    private function hasLongSilence(ShadowInterventionContext $context): bool
    {
        if ($context->currentTimeSeconds() < 120.0) {
            return false;
        }

        $recentLearning = $this->countInteractionsNear($context, ShadowInteractionKind::Question, 90.0)
            + $this->countInteractionsNear($context, ShadowInteractionKind::Answer, 90.0);

        return 0 === $recentLearning;
    }

    private function isImportantSegment(ShadowInterventionContext $context): bool
    {
        $intelligence = $context->videoIntelligence;

        if (null === $intelligence) {
            return false;
        }

        return $intelligence->audio()->confidence()->isLow();
    }

    private function countInteractionsNear(
        ShadowInterventionContext $context,
        ShadowInteractionKind $kind,
        float $windowSeconds,
    ): int {
        $current = $context->currentTimeSeconds();
        $count = 0;

        foreach ($context->session->interactions()->all() as $interaction) {
            if ($interaction->kind() !== $kind) {
                continue;
            }

            $delta = abs($current - $interaction->videoTimestamp()->seconds());

            if ($delta <= $windowSeconds) {
                ++$count;
            }
        }

        return $count;
    }

    private function leadWord(string $text): string
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];

        return trim($words[0] ?? '', '.,;:!?"\'');
    }
}
