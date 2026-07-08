<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

enum RuntimeResolveReason: string
{
    case UserSelection = 'user_selection';
    case HardwareRecommended = 'hardware_recommended';
    case PlannerContext = 'planner_context';
    case CatalogDefault = 'catalog_default';
    case OpsBootstrap = 'ops_bootstrap';
    case Fallback = 'fallback';
    case ProfileRecommended = 'profile_recommended';
    case LockedSelection = 'locked_selection';
    case AnalyticsRecommended = 'analytics_recommended';
    case LearningRecommended = 'learning_recommended';

    public function label(): string
    {
        return match ($this) {
            self::UserSelection => 'User selection',
            self::HardwareRecommended => 'Hardware recommended',
            self::PlannerContext => 'Planner context',
            self::CatalogDefault => 'Catalog default',
            self::OpsBootstrap => 'Ops bootstrap',
            self::Fallback => 'Fallback engine',
            self::ProfileRecommended => 'Profile recommended',
            self::LockedSelection => 'Locked selection',
            self::AnalyticsRecommended => 'Analytics recommended',
            self::LearningRecommended => 'Learning recommended',
        };
    }
}
