# Wav2Lip Installation

Wav2Lip is the **CPU-friendly lip sync** engine for `LOW_END_LOCAL` and `cpu_only` hardware profiles.

## Two-phase install

| Phase | User | Actions |
| --- | --- | --- |
| **System** | `root` | apt packages (if missing), `/models` dirs, `/usr/local/bin/wav2lip` wrapper, ownership |
| **Runtime** | `www-data` | venv, pinned pip wheels, checkpoint download, smoke test |

The Runtime provisioner **never runs apt as www-data**.

```bash
# Full install (root in container)
bash /opt/lumen/install-wav2lip.sh

# Or explicitly
bash /opt/lumen/install-wav2lip.sh --system-only   # root
runuser -u www-data -- bash /opt/lumen/install-wav2lip.sh --runtime-only
```

## Pinned Python stack

Installed into `/models/venvs/wav2lip` (no compile-from-source numpy):

- torch 2.4.1 (CPU or CUDA index)
- torchvision 0.19.1
- numpy 1.26.4
- opencv-python-headless 4.10.0.84
- librosa 0.10.2.post1
- scipy 1.11.4
- numba 0.60.0

## Checkpoint

Required file: `/models/wav2lip/wav2lip_gan.pth` (≥ 100 MB)

Mirrors tried automatically:

1. Hugging Face `Nekochu/Wav2Lip`
2. Hugging Face `numz/wav2lip_studio`
3. Hugging Face `Non-playing-Character/Wav2Lip`
4. `huggingface-cli` / `huggingface_hub` fallback

Official Google Drive / GitHub release links are often expired ([Rudrabha/Wav2Lip#752](https://github.com/Rudrabha/Wav2Lip/issues/752)).

### Manual download

If all mirrors fail, the engine is marked **`MODEL_DOWNLOAD_FAILED`** and `/models/wav2lip/.download-failed` records the reason.

```bash
mkdir -p /models/wav2lip
curl -fsSL -o /models/wav2lip/wav2lip_gan.pth \
  https://huggingface.co/Nekochu/Wav2Lip/resolve/main/wav2lip_gan.pth
chown www-data:www-data /models/wav2lip/wav2lip_gan.pth
rm -f /models/wav2lip/.download-failed
```

Then re-run runtime phase only:

```bash
runuser -u www-data -- bash /opt/lumen/install-wav2lip.sh --runtime-only
```

## Verify

```bash
curl -s http://localhost:8000/api/runtime/readiness | jq '.engines[] | select(.id=="wav2lip")'
make runtime-completion-execute
make runtime-validate
```

## Docker prod-like

System phase runs on container start via `entrypoint.sh`. Runtime provisioning uses:

```bash
make runtime-completion-execute
```

See also: [ENGINE_PROVISIONING.md](./ENGINE_PROVISIONING.md), [RUNTIME_DASHBOARD.md](../architecture/RUNTIME_DASHBOARD.md).
