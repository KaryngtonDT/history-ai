<?php

declare(strict_types=1);

namespace App\Application\RuntimeDashboard;

interface RuntimeDashboardInterface
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array;
}
