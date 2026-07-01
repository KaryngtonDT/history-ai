<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

enum ShadowExplanationStyle: string
{
    case Short = 'short';
    case Detailed = 'detailed';
    case ExampleFirst = 'example_first';
}
