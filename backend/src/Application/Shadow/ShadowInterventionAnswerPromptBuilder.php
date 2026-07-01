<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Chat\ChatPrompt;
use App\Domain\Shadow\ShadowChallengeAnswer;
use App\Domain\Shadow\ShadowIntervention;

final class ShadowInterventionAnswerPromptBuilder
{
    public function build(
        WatchContext $context,
        ShadowIntervention $intervention,
        ShadowChallengeAnswer $answer,
    ): ChatPrompt {
        $challenge = $intervention->challenge()?->questionText() ?? $intervention->expectedUserAction();
        $segment = $context->currentTranscriptSegment?->text ?? $context->nearbyTranscriptContext;

        return new ChatPrompt(<<<PROMPT
You are Shadow, a proactive language-learning tutor watching a video with the user.
Respond briefly and encouragingly to the user's answer.
If the answer is incomplete, guide them without giving the full solution immediately.

Video segment: "{$segment}"
Intervention reason: {$intervention->reason()}
Challenge: {$challenge}
User answer: {$answer->text()}
PROMPT);
    }
}
