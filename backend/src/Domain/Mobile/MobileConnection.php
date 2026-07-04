<?php

declare(strict_types=1);

namespace App\Domain\Mobile;

final readonly class MobileConnection
{
    /** @param list<string> $homeWifiSsids */
    public function __construct(
        private MobileConnectionMode $mode,
        private string $localhostUrl,
        private string $lanUrl,
        private string $tailscaleUrl,
        private array $homeWifiSsids,
    ) {
    }

    public static function createDefault(): self
    {
        return new self(
            MobileConnectionMode::Auto,
            'http://127.0.0.1:8000',
            'http://192.168.178.21:8000',
            'http://100.111.236.50:8000',
            ['FRITZ!Box 7530 BQ'],
        );
    }

    public function mode(): MobileConnectionMode
    {
        return $this->mode;
    }

    public function localhostUrl(): string
    {
        return $this->localhostUrl;
    }

    public function lanUrl(): string
    {
        return $this->lanUrl;
    }

    public function tailscaleUrl(): string
    {
        return $this->tailscaleUrl;
    }

    /** @return list<string> */
    public function homeWifiSsids(): array
    {
        return $this->homeWifiSsids;
    }

    public function withMode(MobileConnectionMode $mode): self
    {
        return new self($mode, $this->localhostUrl, $this->lanUrl, $this->tailscaleUrl, $this->homeWifiSsids);
    }

    /** @param array<string, mixed> $data */
    public function withUpdates(array $data): self
    {
        $mode = is_string($data['mode'] ?? null)
            ? MobileConnectionMode::tryFrom($data['mode']) ?? $this->mode
            : $this->mode;

        $homeWifiSsids = is_array($data['homeWifiSsids'] ?? null)
            ? array_values(array_filter($data['homeWifiSsids'], 'is_string'))
            : $this->homeWifiSsids;

        return new self(
            $mode,
            is_string($data['localhostUrl'] ?? null) ? trim($data['localhostUrl']) : $this->localhostUrl,
            is_string($data['lanUrl'] ?? null) ? trim($data['lanUrl']) : $this->lanUrl,
            is_string($data['tailscaleUrl'] ?? null) ? trim($data['tailscaleUrl']) : $this->tailscaleUrl,
            $homeWifiSsids,
        );
    }
}
