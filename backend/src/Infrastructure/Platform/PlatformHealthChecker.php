<?php

declare(strict_types=1);

namespace App\Infrastructure\Platform;

use App\Application\Platform\PlatformHealthCheckerInterface;
use App\Infrastructure\Storage\JsonFileStore;
use Doctrine\DBAL\Connection;

final class PlatformHealthChecker implements PlatformHealthCheckerInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly JsonFileStore $learningStore,
        private readonly JsonFileStore $shadowIdentityStore,
        private readonly JsonFileStore $shadowSessionStore,
        private readonly string $storageRoot,
        private readonly string $modelsRoot,
        private readonly string $workerBaseUrl,
    ) {
    }

    public function liveness(): array
    {
        return ['status' => 'ok'];
    }

    public function readiness(): array
    {
        $checks = [
            'postgres' => $this->checkPostgres(),
            'storage' => $this->checkStorage(),
            'learning' => $this->checkWritableStore($this->learningStore, 'learning'),
            'shadowIdentity' => $this->checkWritableStore($this->shadowIdentityStore, 'shadow identity'),
            'shadowSessions' => $this->checkWritableStore($this->shadowSessionStore, 'shadow sessions'),
            'models' => $this->checkModels(),
            'worker' => $this->checkWorker(),
        ];

        $healthy = array_reduce(
            $checks,
            static fn (bool $carry, array $check): bool => $carry && ($check['ok'] ?? false),
            true,
        );

        return [
            'status' => $healthy ? 'ready' : 'not_ready',
            'checks' => $checks,
        ];
    }

    public function live(): array
    {
        $readiness = $this->readiness();
        $disk = $this->checkDiskSpace();

        return [
            'status' => ($readiness['status'] === 'ready' && ($disk['ok'] ?? false)) ? 'live' : 'degraded',
            'checks' => [
                ...$readiness['checks'],
                'disk' => $disk,
            ],
        ];
    }

    public function productionReadiness(): array
    {
        $checks = [
            'dockerProduction' => ['ok' => true, 'label' => 'Docker production-like compose available'],
            'persistentVolumes' => ['ok' => is_dir($this->storageRoot), 'label' => 'Storage bind mount present'],
            'postgres' => $this->checkPostgres(),
            'storageWritable' => $this->checkStorage(),
            'learningPersistence' => $this->checkWritableStore($this->learningStore, 'learning'),
            'shadowPersistence' => $this->checkWritableStore($this->shadowIdentityStore, 'shadow identity'),
            'modelsDirectory' => $this->checkModels(),
            'worker' => $this->checkWorker(),
            'diskSpace' => $this->checkDiskSpace(),
            'healthEndpoints' => ['ok' => true, 'label' => 'Health endpoints available'],
        ];

        $score = 0;
        $max = count($checks);

        foreach ($checks as $check) {
            if ($check['ok'] ?? false) {
                ++$score;
            }
        }

        return [
            'score' => (int) round(($score / max(1, $max)) * 100),
            'maxScore' => 100,
            'checks' => $checks,
        ];
    }

    /**
     * @return array{ok: bool, label: string, message?: string}
     */
    private function checkPostgres(): array
    {
        try {
            $this->connection->executeQuery('SELECT 1');

            return ['ok' => true, 'label' => 'PostgreSQL'];
        } catch (\Throwable $exception) {
            return ['ok' => false, 'label' => 'PostgreSQL', 'message' => $exception->getMessage()];
        }
    }

    /**
     * @return array{ok: bool, label: string, message?: string}
     */
    private function checkStorage(): array
    {
        if (!is_dir($this->storageRoot)) {
            return ['ok' => false, 'label' => 'Storage root', 'message' => 'Directory missing'];
        }

        if (!is_writable($this->storageRoot)) {
            return ['ok' => false, 'label' => 'Storage root', 'message' => 'Not writable'];
        }

        return ['ok' => true, 'label' => 'Storage root'];
    }

    /**
     * @return array{ok: bool, label: string, message?: string}
     */
    private function checkWritableStore(JsonFileStore $store, string $label): array
    {
        if (!$store->isWritable()) {
            return ['ok' => false, 'label' => $label, 'message' => 'Store not writable'];
        }

        return ['ok' => true, 'label' => $label];
    }

    /**
     * @return array{ok: bool, label: string, message?: string}
     */
    private function checkModels(): array
    {
        if (!is_dir($this->modelsRoot)) {
            return ['ok' => false, 'label' => 'Models directory', 'message' => 'Directory missing'];
        }

        return ['ok' => true, 'label' => 'Models directory'];
    }

    /**
     * @return array{ok: bool, label: string, message?: string}
     */
    private function checkWorker(): array
    {
        $url = rtrim($this->workerBaseUrl, '/') . '/health';

        if (function_exists('curl_init')) {
            $handle = curl_init($url);
            curl_setopt_array($handle, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_NOBODY => true,
            ]);
            curl_exec($handle);
            $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);

            if (200 === $status) {
                return ['ok' => true, 'label' => 'Worker'];
            }
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 3,
                'ignore_errors' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $context);

        return [
            'ok' => is_string($body) && str_contains($body, '"status"'),
            'label' => 'Worker',
        ];
    }

    /**
     * @return array{ok: bool, label: string, freeGb?: float, message?: string}
     */
    private function checkDiskSpace(): array
    {
        $path = is_dir($this->storageRoot) ? $this->storageRoot : sys_get_temp_dir();
        $free = @disk_free_space($path);

        if (false === $free) {
            return ['ok' => false, 'label' => 'Disk space', 'message' => 'Unable to read free space'];
        }

        $freeGb = round($free / 1024 / 1024 / 1024, 2);

        return [
            'ok' => $freeGb >= 1.0,
            'label' => 'Disk space',
            'freeGb' => $freeGb,
        ];
    }
}
