<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

enum ShadowInterventionTrigger: string
{
    case ImportantSegment = 'important_segment';
    case UnknownVocabulary = 'unknown_vocabulary';
    case LowConfidenceTranslation = 'low_confidence_translation';
    case TopicShift = 'topic_shift';
    case RepeatedConcept = 'repeated_concept';
    case LongSilence = 'long_silence';
    case UserConfusion = 'user_confusion';
    case ManualRequest = 'manual_request';
}
