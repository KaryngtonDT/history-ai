<?php

declare(strict_types=1);

namespace App\Domain\ShadowKnowledge;

enum KnowledgeEdgeType: string
{
    case Prerequisite = 'prerequisite';
    case DependsOn = 'depends_on';
    case Explains = 'explains';
    case Introduces = 'introduces';
    case RelatedTo = 'related_to';
    case Extends = 'extends';
    case ExampleOf = 'example_of';
    case UsedBy = 'used_by';
    case SameTopic = 'same_topic';
    case DerivedFrom = 'derived_from';
}
