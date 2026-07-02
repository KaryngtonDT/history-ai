<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

enum DecisionType: string
{
    case Review = 'review';
    case Learn = 'learn';
    case Skip = 'skip';
    case Pause = 'pause';
    case Accelerate = 'accelerate';
    case SlowDown = 'slow_down';
    case RecommendVideo = 'recommend_video';
    case RecommendPdf = 'recommend_pdf';
    case RecommendAudio = 'recommend_audio';
    case RecommendExercise = 'recommend_exercise';
    case RecommendMission = 'recommend_mission';
    case RecommendRevision = 'recommend_revision';
}
