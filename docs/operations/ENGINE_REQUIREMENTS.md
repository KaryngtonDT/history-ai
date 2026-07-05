# Engine Requirements

## Docker backend image (auto-provisioned)

- PHP 8.4, nginx, ffmpeg
- Python 3 + `faster-whisper`, `yt-dlp`
- CLI shims: `faster-whisper`, `f5-tts`, `openvoice`, `latentsync` (dev placeholders until real weights mounted)

## Host / manual (blocked in Docker)

| Engine | Requirements |
|---|---|
| NVIDIA Parakeet / Canary | CUDA GPU, NeMo toolkit, NGC account |
| Kokoro / Dia | Python env, ONNX/GPU optional |
| Chatterbox / XTTS-v2 | Python, GPU recommended, model license acceptance |
| EchoMimic V2 / MuseTalk | Python, CUDA, large video models |

## Environment variables

See `.env.example` — `STT_*`, `OLLAMA_*`, `TTS_*`, `VOICE_*`, `LIP_*`, `VIDEO_RENDER_*`.

## Volumes

- `lumen-models` → `/models`
- `lumen-storage` → `/var/www/html/storage`
