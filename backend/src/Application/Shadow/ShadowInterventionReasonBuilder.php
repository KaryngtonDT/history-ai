<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Shadow\ShadowInterventionTrigger;

final class ShadowInterventionReasonBuilder
{
    public function build(
        ShadowInterventionTrigger $trigger,
        ShadowInterventionContext $context,
        ?string $detail = null,
    ): string {
        $base = match ($trigger) {
            ShadowInterventionTrigger::UnknownVocabulary => sprintf(
                'Shadow noticed uncommon vocabulary%s that may need clarification.',
                null !== $detail ? sprintf(' ("%s")', $detail) : '',
            ),
            ShadowInterventionTrigger::LowConfidenceTranslation => 'Translation confidence appears low for this segment.',
            ShadowInterventionTrigger::TopicShift => 'The topic appears to shift here; a quick summary may help.',
            ShadowInterventionTrigger::RepeatedConcept => 'You paused near this concept several times.',
            ShadowInterventionTrigger::LongSilence => 'You have been watching for a while without checking in.',
            ShadowInterventionTrigger::UserConfusion => 'Recent pauses suggest this section may be unclear.',
            ShadowInterventionTrigger::ImportantSegment => 'This segment looks important for understanding the video.',
            ShadowInterventionTrigger::ManualRequest => 'You asked Shadow to check this moment.',
        };

        if (null !== $context->userPreferenceProfile) {
            $style = $context->userPreferenceProfile->translationStyle()->value;

            return $base.' Shadow adapted to your preferred '.$style.' translation style.';
        }

        return $base;
    }
}
