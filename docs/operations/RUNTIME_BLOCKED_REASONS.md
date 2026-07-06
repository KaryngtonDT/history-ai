# Runtime Blocked Reasons

Structured codes returned by the compatibility API.

## Codes

| Code | Severity | Typical fix |
|---|---|---|
| `nvidia_cuda_required` | blocking | Remote GPU or Wav2Lip |
| `gpu_not_found` | blocking | Upgrade hardware |
| `vram_insufficient` | blocking | Smaller model or remote GPU |
| `ram_insufficient` | blocking | Free RAM / increase WSL memory |
| `model_missing` | blocking | `make provision ENGINE=<id>` |
| `binary_missing` | blocking | Install engine binary |
| `python_env_missing` | blocking | Install Python deps |
| `docker_gpu_not_available` | blocking | Enable GPU in Docker |
| `nvenc_requires_nvidia` | blocking | Use FFmpeg AV1 |
| `unsupported_on_current_provider` | blocking | Change provider |
| `optional_language_pack_missing` | info | Install pack if language enabled |
| `not_installed` | blocking | Provision engine |

## Response shape

```json
{
  "engineId": "latentsync",
  "status": "blocked",
  "hardwareProfile": "low_end_local",
  "blockedReasonCode": "nvidia_cuda_required",
  "humanReason": "LatentSync requires an NVIDIA CUDA GPU with high VRAM...",
  "missingRequirements": ["NVIDIA GPU", "CUDA", "18 GB VRAM"],
  "recommendedAlternative": "wav2lip",
  "canBeFixedByInstall": false,
  "canBeFixedByHardware": true,
  "canBeFixedByRemoteProvider": true,
  "fixTypes": ["use_compatible_alternative", "upgrade_hardware", "use_remote_gpu_provider"]
}
```

## UI

`/settings/runtime` shows status badge, hardware compatibility badge, expandable “Why blocked?” panel, missing requirements, recommended alternative, and fix types.
