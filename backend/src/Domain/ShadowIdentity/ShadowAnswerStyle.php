<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

enum ShadowAnswerStyle: string
{
    case Short = 'short';
    case Detailed = 'detailed';
    case ExampleRich = 'example_rich';
    case StoryDriven = 'story_driven';
}
