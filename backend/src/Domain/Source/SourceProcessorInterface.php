<?php

declare(strict_types=1);

namespace App\Domain\Source;

interface SourceProcessorInterface
{
    public function supports(SourceType $type): bool;

    public function process(Source $source, SourceProcessingRequest $request): SourceProcessingResult;
}
