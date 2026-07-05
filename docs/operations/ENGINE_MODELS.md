# Engine Models

Official model tags and mount paths for Lumen supported engines.

| Engine | Model tag / artifact | Mount path |
|---|---|---|
| Faster Whisper Large V3 | HuggingFace `large-v3` | HF cache (env `STT_FASTER_WHISPER_MODEL=large-v3`) |
| Ollama + Gemma 3 | `gemma3:4b` | Ollama volume |
| Ollama + Qwen 3 | `qwen3:4b` | Ollama volume |
| Ollama + DeepSeek R1 Distill | `deepseek-r1:1.5b` | Ollama volume |
| F5-TTS | SWivid/F5-TTS checkpoints | `/models/f5` |
| OpenVoice V2 | myshell-ai/OpenVoiceV2 | `/models/openvoice` |
| LatentSync | LatentSync weights | `/models/latentsync` |

Pull Ollama models:

```bash
docker compose exec ollama ollama pull gemma3:4b
docker compose exec ollama ollama pull qwen3:4b
docker compose exec ollama ollama pull deepseek-r1:1.5b
```

Prefetch Faster Whisper:

```bash
docker compose exec backend python3 -c "from faster_whisper import WhisperModel; WhisperModel('large-v3', device='cpu', compute_type='int8')"
```

See `ENGINE_INSTALLATION.md` for blocked engines (Parakeet, Canary, Kokoro, etc.).
