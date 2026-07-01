<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

enum ShadowInterventionType: string
{
    case Explanation = 'explanation';
    case ChallengeQuestion = 'challenge_question';
    case VocabularyCheck = 'vocabulary_check';
    case GrammarCheck = 'grammar_check';
    case ConceptCheck = 'concept_check';
    case SummaryPrompt = 'summary_prompt';
    case ReflectionPrompt = 'reflection_prompt';
}
