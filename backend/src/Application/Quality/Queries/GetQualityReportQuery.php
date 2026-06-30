<?php

declare(strict_types=1);

namespace App\Application\Quality\Queries;

final readonly class GetQualityReportQuery
{
    public function __construct(public string $videoId)
    {
    }
}
