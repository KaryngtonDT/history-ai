<?php

declare(strict_types=1);

namespace App\Domain\Engine;

enum SelectionMode: string
{
    case Auto = 'auto';
    case Profile = 'profile';
    case Manual = 'manual';
    case Rules = 'rules';
}
