# Sprint 70.7 — Verification

## Wav2Lip provisioning fix (3 causes addressed)

| Cause | Fix |
| --- | --- |
| `www-data` running `apt` | Split installer: `--system-only` (root) + `--runtime-only` (www-data); entrypoint runs system phase |
| numpy compile from legacy requirements | Pinned wheels only — no `requirements.txt` pip install; `setuptools<82` |
| Checkpoint URL failure | Hugging Face mirrors (`Nekochu/Wav2Lip`, etc.) + `MODEL_DOWNLOAD_FAILED` + manual path in `WAV2LIP_INSTALLATION.md` |

## Checks

| Check | Result |
| --- | --- |
| `install-wav2lip.sh --system-only` (root) | PASS |
| `install-wav2lip.sh --runtime-only` (www-data) | PASS |
| Wav2Lip READY in readiness | **PASS** (`status: ready`, `modelFound: true`) |
| Wav2Lip benchmark | **PASS** (`ok: true`, 32.73 ms) |
| `runtime-completion-execute` | PASS (`compatibleEngineCompletionPlan: []`, wav2lip already ready) |
| Runtime score after fix | 44.8 (9/22 compatible engines READY, was 8/22) |
| PHPUnit / Vitest | PHPUnit: 1793 tests (after test fixes for `BlockedReasonResolver`) |

## Commands run

```bash
docker compose -f docker-compose.prod-like.yml up -d --build backend
docker compose -f docker-compose.prod-like.yml exec -u root backend bash /opt/lumen/install-wav2lip.sh
curl -X POST http://localhost:8000/api/runtime/completion/execute
curl -X POST http://localhost:8000/api/runtime/pipeline/validate
curl -X POST http://localhost:8000/api/runtime/benchmark/full
docker compose -f docker-compose.prod-like.yml exec backend php bin/phpunit
```

## Notes

- Official Wav2Lip Google Drive / GitHub release URLs are expired ([Rudrabha/Wav2Lip#752](https://github.com/Rudrabha/Wav2Lip/issues/752)); use Hugging Face community mirrors.
- Pipeline validation still requests `wav2lip` but executes `latentsync` for lip_sync (separate pipeline default config — not a provisioning failure).
- Wav2Lip checkpoint: `/models/wav2lip/wav2lip_gan.pth` (~416 MB from `Nekochu/Wav2Lip`).
