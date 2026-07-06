# Sprint 70.45 — Verification

## Acceptance criteria

| Criterion | Status |
|---|---|
| Every blocked engine has human-readable reason | ✅ |
| UI shows why engine is blocked | ✅ |
| Runtime knows detected hardware profile | ✅ |
| Machine classified as `low_end_local` (AMD iGPU) | ✅ |
| LatentSync + EchoMimic blocked (NVIDIA/CUDA) | ✅ |
| Wav2Lip recommended for lip sync | ✅ |
| No READY without true executability | ✅ |
| No silent fallback | ✅ |

## Engine compatibility table (low-end local reference)

| Engine | Status | Blocked Reason | Missing Requirements | Recommended Alternative | Fix Type |
|---|---|---|---|---|---|
| latentsync | blocked | nvidia_cuda_required | NVIDIA GPU, CUDA, 18 GB VRAM | wav2lip | use_compatible_alternative, upgrade_hardware, use_remote_gpu_provider |
| echomimic_v2 | blocked | nvidia_cuda_required | NVIDIA GPU, CUDA, 12 GB VRAM | wav2lip | use_compatible_alternative, upgrade_hardware, use_remote_gpu_provider |
| wav2lip | missing/blocked | binary_missing / not_installed | Engine binary, Model files | — | install_dependency, install_model |
| ffmpeg | ready | none | — | — | — |
| ffmpeg_nvenc | blocked | nvenc_requires_nvidia | NVIDIA GPU, NVENC | ffmpeg_av1 | use_compatible_alternative, upgrade_hardware |
| ffmpeg_av1 | ready* | none | — | — | — |
| openvoice_v2 | ready* | none | — | — | — |
| f5_tts | ready* | none | — | — | — |

\*Status depends on actual installation state; hardware compatibility is `true`.

## Tests

```bash
make test
make doctor
make runtime-validate
```

### Backend

- `HardwareProfileClassifierTest`
- `EngineCompatibilityEvaluatorTest`
- `RuntimeHardwareControllerTest`

### Frontend

- `RuntimeCenter.test.tsx` — hardware profile, blocked explanation, recommended alternative

## API smoke

```bash
curl /api/runtime/hardware
curl /api/runtime/compatibility
curl /api/runtime/engines/latentsync/blocked-reason
```

## Commit

```
feat(runtime): add hardware profiles and blocked engine explainability
```
