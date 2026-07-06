<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Provisioning;

use App\Domain\Engine\EngineRepositoryInterface;
use App\Domain\Runtime\RuntimeStatus;
use App\Infrastructure\Runtime\Benchmark\EngineTestRunner;
use Symfony\Component\Process\Process;

final class EngineProvisioner
{
    public function __construct(
        private readonly EngineRepositoryInterface $engineRepository,
        private readonly EngineTestRunner $engineTestRunner,
        private readonly ProvisioningCompatibilityGate $compatibilityGate,
        private readonly string $ollamaBaseUrl,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function provision(string $engineId): array
    {
        $blocked = $this->compatibilityGate->blockedPayload($engineId);
        if (null !== $blocked) {
            return $blocked;
        }

        $spec = EngineProvisioningCatalog::find($engineId);

        if (null === $spec) {
            return $this->result($engineId, false, RuntimeStatus::Blocked, 'Engine not found in provisioning catalog.');
        }

        if (!$spec->autoProvisionSupported) {
            return $this->blockedResult($engineId, $spec);
        }

        $started = microtime(true);
        $output = [];
        $ok = match ($engineId) {
            'faster_whisper_large_v3' => $this->provisionFasterWhisper($output),
            'ollama_gemma3' => $this->provisionOllamaModel('gemma3:4b', $output),
            'ollama_qwen3' => $this->provisionOllamaModel('qwen3:4b', $output),
            'ollama_deepseek_r1_distill' => $this->provisionOllamaModel('deepseek-r1:1.5b', $output),
            'f5_tts' => $this->provisionGpuEngine('f5', $output),
            'openvoice_v2' => $this->provisionGpuEngine('openvoice', $output),
            'latentsync' => $this->provisionGpuEngine('latentsync', $output),
            'wav2lip' => $this->provisionWav2Lip($output),
            'whisper_cpp' => $this->provisionWhisperCpp($output),
            'piper' => $this->provisionPiper($output),
            'ffmpeg', 'ffmpeg_nvenc', 'ffmpeg_av1' => $this->verifyFfmpeg($output),
            default => false,
        };

        $engine = $this->engineRepository->findById($engineId);
        $test = $this->engineTestRunner->run($engineId);
        $durationMs = round((microtime(true) - $started) * 1000, 2);

        if ($ok && null !== $engine && $engine->isReady()) {
            return [
                'engineId' => $engineId,
                'status' => RuntimeStatus::Ready->value,
                'provisioned' => true,
                'ok' => true,
                'durationMs' => $durationMs,
                'output' => $output,
                'test' => $test,
            ];
        }

        $reason = $engine?->errorReason ?? 'Automatic provisioning did not reach READY state.';

        return [
            'engineId' => $engineId,
            'status' => RuntimeStatus::Blocked->value,
            'provisioned' => false,
            'ok' => false,
            'durationMs' => $durationMs,
            'output' => $output,
            'test' => $test,
            'blockedReason' => $reason,
            'installCommand' => $spec->installCommand,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function provisionAll(): array
    {
        $results = [];

        foreach (EngineProvisioningCatalog::all() as $spec) {
            $results[] = $this->provision($spec->engineId);
        }

        $ready = count(array_filter($results, static fn (array $r): bool => ($r['status'] ?? '') === RuntimeStatus::Ready->value));
        $blocked = count(array_filter($results, static fn (array $r): bool => ($r['status'] ?? '') === RuntimeStatus::Blocked->value));

        return [
            'ok' => $blocked === 0,
            'readyCount' => $ready,
            'blockedCount' => $blocked,
            'totalCount' => count($results),
            'results' => $results,
            'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];
    }

    /**
     * @param list<string> $output
     */
    private function provisionFasterWhisper(array &$output): bool
    {
        $process = Process::fromShellCommandline(
            'python3 -c "from faster_whisper import WhisperModel; WhisperModel(\'large-v3\', device=\'cpu\', compute_type=\'int8\')" 2>&1',
        );
        $process->setTimeout(600);
        $process->run();
        $output[] = trim($process->getOutput().$process->getErrorOutput());

        return $process->isSuccessful();
    }

    /**
     * @param list<string> $output
     */
    private function provisionOllamaModel(string $model, array &$output): bool
    {
        $url = rtrim($this->ollamaBaseUrl, '/').'/api/pull';
        $payload = json_encode(['name' => $model], JSON_THROW_ON_ERROR);
        $process = Process::fromShellCommandline(sprintf(
            'curl -sf -X POST %s -H "Content-Type: application/json" -d %s',
            escapeshellarg($url),
            escapeshellarg($payload),
        ));
        $process->setTimeout(900);
        $process->run();
        $output[] = trim($process->getOutput().$process->getErrorOutput());

        return $process->isSuccessful();
    }

    /**
     * @param list<string> $output
     */
    private function provisionWav2Lip(array &$output): bool
    {
        $process = Process::fromShellCommandline(
            'bash /opt/lumen/install-wav2lip.sh 2>&1',
        );
        $process->setTimeout(7200);
        $process->run();
        $output[] = trim($process->getOutput().$process->getErrorOutput());

        return $process->isSuccessful();
    }

    /**
     * @param list<string> $output
     */
    private function provisionWhisperCpp(array &$output): bool
    {
        $process = Process::fromShellCommandline(
            'bash /opt/lumen/install-whisper-cpp.sh 2>&1',
        );
        $process->setTimeout(600);
        $process->run();
        $output[] = trim($process->getOutput().$process->getErrorOutput());

        return $process->isSuccessful();
    }

    /**
     * @param list<string> $output
     */
    private function provisionPiper(array &$output): bool
    {
        $process = Process::fromShellCommandline(
            'bash /opt/lumen/install-piper.sh 2>&1',
        );
        $process->setTimeout(600);
        $process->run();
        $output[] = trim($process->getOutput().$process->getErrorOutput());

        return $process->isSuccessful();
    }

    /**
     * @param list<string> $output
     */
    private function provisionGpuEngine(string $engine, array &$output): bool
    {
        $process = Process::fromShellCommandline(
            'bash /opt/lumen/install-gpu-engines.sh --engine '.escapeshellarg($engine).' 2>&1',
        );
        $process->setTimeout(7200);
        $process->run();
        $output[] = trim($process->getOutput().$process->getErrorOutput());

        return $process->isSuccessful();
    }

    /**
     * @param list<string> $output
     */
    private function verifyFfmpeg(array &$output): bool
    {
        $process = Process::fromShellCommandline('ffmpeg -version 2>&1');
        $process->run();
        $output[] = strtok(trim($process->getOutput()), "\n") ?: '';

        return $process->isSuccessful();
    }

    /**
     * @return array<string, mixed>
     */
    private function blockedResult(string $engineId, EngineProvisionSpec $spec): array
    {
        return [
            'engineId' => $engineId,
            'status' => RuntimeStatus::Blocked->value,
            'provisioned' => false,
            'ok' => false,
            'blockedReason' => $spec->blockedReason,
            'installCommand' => $spec->installCommand,
            'modelDownloadHint' => $spec->modelDownloadHint,
            'modelPath' => $spec->modelPath,
            'documentationPath' => $spec->documentationPath,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function result(string $engineId, bool $ok, RuntimeStatus $status, string $message): array
    {
        return [
            'engineId' => $engineId,
            'status' => $status->value,
            'provisioned' => $ok,
            'ok' => $ok,
            'blockedReason' => $ok ? null : $message,
        ];
    }
}
