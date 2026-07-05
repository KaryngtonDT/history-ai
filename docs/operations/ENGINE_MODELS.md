# Engine Models

Official model tags and mount paths for Lumen supported engines.

## Ollama translation — local defaults vs larger alternatives

Lumen uses **lightweight tags** for local/dev machines. The Runtime Registry matches by family prefix (`gemma3`, `qwen3`, `deepseek-r1`), so any pulled variant with that prefix is detected.

| Engine | Recommended local tag | Size (approx.) | Larger alternatives (more VRAM/RAM) |
|---|---|---|---|
| Ollama + Gemma 3 | `gemma3:4b` | ~3.3 GB | `gemma3:12b`, `gemma3:27b` |
| Ollama + Qwen 3 | `qwen3:4b` | ~2.5 GB | `qwen3:8b`, `qwen3:14b`, `qwen3:32b` |
| Ollama + DeepSeek R1 Distill | `deepseek-r1:1.5b` | ~1.1 GB | `deepseek-r1:7b`, `deepseek-r1:8b`, `deepseek-r1:14b` |

Registry engine IDs: `ollama_gemma3`, `ollama_qwen3`, `ollama_deepseek_r1_distill`.  
Default pipeline model: `OLLAMA_MODEL=gemma3:4b`.

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
