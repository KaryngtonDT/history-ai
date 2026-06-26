<?php

declare(strict_types=1);

namespace App\Infrastructure\Processing;

use App\Application\Processing\Ports\ProcessingOrchestratorInterface;
use App\Domain\Processing\ProcessingJob;
use RuntimeException;

final class HttpWorkerDispatcher implements ProcessingOrchestratorInterface
{
    public function __construct(
        private readonly string $workerBaseUrl,
    ) {
    }

    public function dispatch(ProcessingJob $job): void
    {
        $payload = json_encode([
            'processingJobId' => $job->id()->value,
            'contentId' => $job->contentId()->value,
            'type' => $job->type()->value,
        ], JSON_THROW_ON_ERROR);

        $url = sprintf('%s/jobs/execute', rtrim($this->workerBaseUrl, '/'));
        $handle = curl_init($url);

        if (false === $handle) {
            throw new RuntimeException('Unable to initialize worker dispatch request.');
        }

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
        ]);

        $responseBody = curl_exec($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ('' !== $error) {
            throw new RuntimeException(sprintf('Worker dispatch failed: %s', $error));
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException(sprintf(
                'Worker dispatch failed with HTTP %d: %s',
                $statusCode,
                is_string($responseBody) ? $responseBody : '',
            ));
        }
    }
}
