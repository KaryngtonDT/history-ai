<?php

declare(strict_types=1);

namespace App\Application\RuntimeCompletion;

interface RuntimeCompletionInterface
{
    /**
     * @return array<string, mixed>
     */
    public function plan(): array;

    /**
     * @return array<string, mixed>
     */
    public function execute(): array;
}
