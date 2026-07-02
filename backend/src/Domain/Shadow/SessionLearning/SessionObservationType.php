<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

enum SessionObservationType: string
{
    case Pause = 'pause';
    case Resume = 'resume';
    case Question = 'question';
    case RepeatedQuestion = 'repeated_question';
    case Replay = 'replay';
    case Skip = 'skip';
    case ChallengeSuccess = 'challenge_success';
    case ChallengeSkip = 'challenge_skip';
    case SlowResponse = 'slow_response';
    case FastResponse = 'fast_response';
}
