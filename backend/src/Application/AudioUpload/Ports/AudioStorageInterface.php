<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Ports;

use App\Domain\Source\SourceId;

interface AudioStorageInterface
{
    public function store(SourceId $audioId, string $sourcePath, string $originalFilename): string;

    public function delete(string $storagePath): void;
}
