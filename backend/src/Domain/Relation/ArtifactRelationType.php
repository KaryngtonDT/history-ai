<?php

declare(strict_types=1);

namespace App\Domain\Relation;

enum ArtifactRelationType: string
{
    case Related = 'related';
    case DerivedFrom = 'derived_from';
    case References = 'references';
    case Next = 'next';
    case Previous = 'previous';
}
