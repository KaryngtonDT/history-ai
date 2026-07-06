# Engine Compatibility

Each catalog engine declares explicit hardware requirements. Compatibility is evaluated separately from installation readiness.

## Status values

| Status | Meaning |
|---|---|
| `ready` | Executable on current hardware and installed |
| `blocked` | Hardware or environment prevents execution |
| `misconfigured` | Installed but misconfigured |
| `mock` | Mock/shim mode |
| `missing` | Binary or model not installed |

## Requirement dimensions

- GPU vendor (NVIDIA for CUDA engines)
- CUDA required vs recommended
- Minimum VRAM and RAM
- CPU fallback supported
- Provider support: HOST / DOCKER / REMOTE
- NVENC for FFmpeg NVENC variant

## Examples on low-end local hardware

| Engine | Hardware compatible | Local fallback |
|---|---|---|
| LatentSync | No | Wav2Lip |
| EchoMimic V2 | No | Wav2Lip |
| Wav2Lip | Yes (CPU, slow) | — |
| FFmpeg | Yes | — |
| FFmpeg NVENC | No | FFmpeg AV1 |
| OpenVoice V2 | Yes | — |

## API

- `GET /api/runtime/compatibility`
- `GET /api/runtime/engines/{engineId}/compatibility`
- `GET /api/runtime/engines/{engineId}/blocked-reason`

Readiness responses also embed a `compatibility` object per engine.

## Rules

1. No engine is `ready` unless hardware-compatible **and** truly executable.
2. Blocked engines remain visible with human-readable reasons.
3. No silent fallback — alternatives are recommendations only.
