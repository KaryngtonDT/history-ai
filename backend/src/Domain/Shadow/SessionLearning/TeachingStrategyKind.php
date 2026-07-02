<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

enum TeachingStrategyKind: string
{
    case Balanced = 'balanced';
    case ExampleDriven = 'example_driven';
    case ChallengeFocused = 'challenge_focused';
    case ConciseSupport = 'concise_support';
    case StoryBased = 'story_based';
    case Recovery = 'recovery';
}
