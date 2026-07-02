<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

enum ShadowTeachingStyle: string
{
    case ExampleFirst = 'example_first';
    case PrincipleFirst = 'principle_first';
    case Visual = 'visual';
    case Quiz = 'quiz';
    case StoryBased = 'story_based';
    case Debate = 'debate';
    case Exercise = 'exercise';
}
