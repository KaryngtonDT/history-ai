# AI Models

Model weights are stored here and mounted at `/models` in backend and worker containers.

**Never bake models into Docker images.** Rebuilding images must not re-download multi-GB weights.

## Expected layout

```
models/
├── whisper/      # Faster-Whisper
├── ollama/       # Ollama models
├── f5/           # F5-TTS
├── openvoice/    # OpenVoice V2
└── latentsync/   # LatentSync
```
