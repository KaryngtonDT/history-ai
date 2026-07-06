<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Provisioning;

final readonly class EngineProvisionSpec
{
    public function __construct(
        public string $engineId,
        public bool $autoProvisionSupported,
        public ?string $blockedReason = null,
        public ?string $installCommand = null,
        public ?string $modelDownloadHint = null,
        public ?string $modelPath = null,
        public ?string $documentationPath = null,
    ) {
    }
}

final class EngineProvisioningCatalog
{
    /**
     * @return list<EngineProvisionSpec>
     */
    public static function all(): array
    {
        return [
            new EngineProvisionSpec(
                'faster_whisper_large_v3',
                true,
                modelPath: null,
                installCommand: 'make provision-engines (prefetches HuggingFace large-v3)',
                documentationPath: 'docs/operations/ENGINE_INSTALLATION.md#speech-to-text',
            ),
            new EngineProvisionSpec(
                'whisper_cpp',
                true,
                installCommand: 'bash /opt/lumen/install-whisper-cpp.sh',
                modelDownloadHint: 'https://github.com/ggerganov/whisper.cpp',
                modelPath: '/models/whisper-cpp',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#speech-to-text',
            ),
            new EngineProvisionSpec(
                'parakeet',
                false,
                blockedReason: 'NVIDIA Parakeet requires NeMo toolkit, CUDA, and manual model download from NVIDIA NGC.',
                installCommand: 'See docs/operations/ENGINE_INSTALLATION.md#nvidia-parakeet',
                modelDownloadHint: 'https://catalog.ngc.nvidia.com/orgs/nvidia/models/nemo_parakeet',
                modelPath: '/models/parakeet',
                documentationPath: 'docs/operations/ENGINE_INSTALLATION.md#nvidia-parakeet',
            ),
            new EngineProvisionSpec(
                'canary',
                false,
                blockedReason: 'NVIDIA Canary requires NeMo toolkit, CUDA, and manual model download from NVIDIA NGC.',
                installCommand: 'See docs/operations/ENGINE_INSTALLATION.md#nvidia-canary',
                modelDownloadHint: 'https://catalog.ngc.nvidia.com/orgs/nvidia/models/nemo_canary',
                modelPath: '/models/canary',
                documentationPath: 'docs/operations/ENGINE_INSTALLATION.md#nvidia-canary',
            ),
            new EngineProvisionSpec(
                'ollama_gemma3',
                true,
                installCommand: 'docker compose exec ollama ollama pull gemma3:4b',
                modelDownloadHint: 'gemma3:4b via Ollama',
                documentationPath: 'docs/operations/ENGINE_MODELS.md#ollama-gemma-3',
            ),
            new EngineProvisionSpec(
                'ollama_qwen3',
                true,
                installCommand: 'docker compose exec ollama ollama pull qwen3:4b',
                modelDownloadHint: 'qwen3:4b via Ollama',
                documentationPath: 'docs/operations/ENGINE_MODELS.md#ollama-qwen-3',
            ),
            new EngineProvisionSpec(
                'ollama_deepseek_r1_distill',
                true,
                installCommand: 'docker compose exec ollama ollama pull deepseek-r1:1.5b',
                modelDownloadHint: 'deepseek-r1:1.5b via Ollama',
                documentationPath: 'docs/operations/ENGINE_MODELS.md#ollama-deepseek-r1',
            ),
            new EngineProvisionSpec(
                'f5_tts',
                true,
                installCommand: 'make install-gpu-engines or: docker compose exec backend bash /opt/lumen/install-gpu-engines.sh --engine f5',
                modelDownloadHint: 'https://huggingface.co/SWivid/F5-TTS',
                modelPath: '/models/f5',
                documentationPath: 'docs/operations/ENGINE_INSTALL_F5_OPENVOICE_LATENTSYNC.md#1-f5-tts',
            ),
            new EngineProvisionSpec(
                'kokoro',
                false,
                blockedReason: 'Kokoro TTS is not packaged in the Lumen Docker image. Install kokoro on the host and expose the CLI on PATH.',
                installCommand: 'pip install kokoro-onnx && download voices to /models/kokoro',
                modelDownloadHint: 'https://github.com/thewh1teagle/kokoro-onnx',
                modelPath: '/models/kokoro',
                documentationPath: 'docs/operations/ENGINE_INSTALLATION.md#kokoro-tts',
            ),
            new EngineProvisionSpec(
                'piper',
                true,
                installCommand: 'bash /opt/lumen/install-piper.sh',
                modelDownloadHint: 'https://github.com/rhasspy/piper',
                modelPath: '/models/piper',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#text-to-speech',
            ),
            new EngineProvisionSpec(
                'dia',
                false,
                blockedReason: 'Dia TTS requires manual installation from the upstream repository and model download.',
                installCommand: 'See docs/operations/ENGINE_INSTALLATION.md#dia-tts',
                modelDownloadHint: 'https://github.com/nari-labs/dia',
                modelPath: '/models/dia',
                documentationPath: 'docs/operations/ENGINE_INSTALLATION.md#dia-tts',
            ),
            new EngineProvisionSpec(
                'openvoice_v2',
                true,
                installCommand: 'make install-gpu-engines or: docker compose exec backend bash /opt/lumen/install-gpu-engines.sh --engine openvoice',
                modelDownloadHint: 'https://huggingface.co/myshell-ai/OpenVoiceV2',
                modelPath: '/models/openvoice',
                documentationPath: 'docs/operations/ENGINE_INSTALL_F5_OPENVOICE_LATENTSYNC.md#2-openvoice-v2',
            ),
            new EngineProvisionSpec(
                'chatterbox',
                false,
                blockedReason: 'Chatterbox voice clone requires manual install and GPU setup.',
                installCommand: 'See docs/operations/ENGINE_INSTALLATION.md#chatterbox',
                modelPath: '/models/chatterbox',
                documentationPath: 'docs/operations/ENGINE_INSTALLATION.md#chatterbox',
            ),
            new EngineProvisionSpec(
                'xtts_v2',
                false,
                blockedReason: 'Coqui XTTS-v2 requires manual install (coqui-tts) and model acceptance.',
                installCommand: 'pip install coqui-tts && download XTTS-v2 to /models/xtts',
                modelDownloadHint: 'https://github.com/coqui-ai/TTS',
                modelPath: '/models/xtts',
                documentationPath: 'docs/operations/ENGINE_INSTALLATION.md#xtts-v2',
            ),
            new EngineProvisionSpec(
                'latentsync',
                true,
                installCommand: 'make install-gpu-engines or: docker compose exec backend bash /opt/lumen/install-gpu-engines.sh --engine latentsync',
                modelDownloadHint: 'https://github.com/bytedance/LatentSync',
                modelPath: '/models/latentsync',
                documentationPath: 'docs/operations/LATENTSYNC_INSTALLATION.md',
            ),
            new EngineProvisionSpec(
                'liveportrait',
                false,
                blockedReason: 'LivePortrait requires NVIDIA GPU, manual install, and model weights.',
                installCommand: 'bash /opt/lumen/install-liveportrait.sh',
                modelDownloadHint: 'https://github.com/KwaiVGI/LivePortrait',
                modelPath: '/models/liveportrait',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#lip-sync',
            ),
            new EngineProvisionSpec(
                'echomimic_v2',
                false,
                blockedReason: 'EchoMimic V2 requires manual GPU install and model download.',
                installCommand: 'See docs/operations/ENGINE_INSTALLATION.md#echomimic-v2',
                modelPath: '/models/echomimic',
                documentationPath: 'docs/operations/ENGINE_INSTALLATION.md#echomimic-v2',
            ),
            new EngineProvisionSpec(
                'wav2lip',
                true,
                installCommand: 'bash /opt/lumen/install-wav2lip.sh',
                modelDownloadHint: 'https://github.com/Rudrabha/Wav2Lip',
                modelPath: '/models/wav2lip',
                documentationPath: 'docs/operations/ENGINE_PROVISIONING.md#wav2lip',
            ),
            new EngineProvisionSpec(
                'musetalk',
                false,
                blockedReason: 'MuseTalk is legacy — requires NVIDIA GPU and manual install. Prefer Wav2Lip on CPU-only hosts.',
                installCommand: 'bash /opt/lumen/install-musetalk.sh',
                modelDownloadHint: 'https://github.com/TMElyralab/MuseTalk',
                modelPath: '/models/musetalk',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#lip-sync',
            ),
            new EngineProvisionSpec(
                'ffmpeg',
                true,
                installCommand: 'Bundled in backend Docker image (apt ffmpeg)',
                documentationPath: 'docs/operations/ENGINE_INSTALLATION.md#ffmpeg',
            ),
            new EngineProvisionSpec(
                'ffmpeg_nvenc',
                true,
                installCommand: 'Requires NVIDIA GPU + ffmpeg build with NVENC (verify with ffmpeg -encoders)',
                documentationPath: 'docs/operations/ENGINE_INSTALLATION.md#ffmpeg-nvenc',
            ),
            new EngineProvisionSpec(
                'ffmpeg_av1',
                true,
                installCommand: 'Requires ffmpeg build with libaom-av1 (verify with ffmpeg -encoders)',
                documentationPath: 'docs/operations/ENGINE_INSTALLATION.md#ffmpeg-av1',
            ),

            // OCR
            new EngineProvisionSpec(
                'paddleocr',
                false,
                blockedReason: 'PaddleOCR requires manual pip install and model download.',
                installCommand: 'pip install paddlepaddle paddleocr',
                modelPath: '/models/paddleocr',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#ocr',
            ),
            new EngineProvisionSpec(
                'easyocr',
                false,
                blockedReason: 'EasyOCR requires manual pip install and language packs.',
                installCommand: 'pip install easyocr',
                modelPath: '/models/easyocr',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#ocr',
            ),

            // Vision
            new EngineProvisionSpec(
                'florence_2',
                false,
                blockedReason: 'Florence-2 requires HuggingFace model download and GPU for best performance.',
                installCommand: 'pip install transformers torch && download microsoft/Florence-2-base',
                modelPath: '/models/florence-2',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#vision',
            ),
            new EngineProvisionSpec(
                'qwen2_5_vl',
                false,
                blockedReason: 'Qwen2.5-VL requires NVIDIA GPU and large VRAM.',
                installCommand: 'See HuggingFace Qwen2.5-VL model card',
                modelPath: '/models/qwen2-vl',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#vision',
            ),
            new EngineProvisionSpec(
                'smolvlm',
                false,
                blockedReason: 'SmolVLM requires manual install from HuggingFace.',
                installCommand: 'pip install transformers && download HuggingFaceTB/SmolVLM',
                modelPath: '/models/smolvlm',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#vision',
            ),

            // Embeddings
            new EngineProvisionSpec(
                'bge_m3',
                false,
                blockedReason: 'BGE-M3 embedding model requires manual install (FlagEmbedding or sentence-transformers).',
                installCommand: 'pip install FlagEmbedding',
                modelPath: '/models/bge-m3',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#embeddings',
            ),
            new EngineProvisionSpec(
                'nomic_embed',
                false,
                blockedReason: 'Nomic Embed requires manual model download.',
                installCommand: 'pip install sentence-transformers',
                modelPath: '/models/nomic-embed',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#embeddings',
            ),
            new EngineProvisionSpec(
                'jina_embeddings',
                false,
                blockedReason: 'Jina Embeddings requires manual install.',
                installCommand: 'pip install sentence-transformers',
                modelPath: '/models/jina-embeddings',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#embeddings',
            ),
            new EngineProvisionSpec(
                'e5_large',
                false,
                blockedReason: 'E5 Large requires manual model download.',
                installCommand: 'pip install sentence-transformers',
                modelPath: '/models/e5-large',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#embeddings',
            ),

            // Reranking
            new EngineProvisionSpec(
                'bge_reranker',
                false,
                blockedReason: 'BGE Reranker requires manual install (FlagEmbedding).',
                installCommand: 'pip install FlagEmbedding',
                modelPath: '/models/bge-reranker',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#reranking',
            ),
            new EngineProvisionSpec(
                'jina_reranker',
                false,
                blockedReason: 'Jina Reranker requires manual install.',
                installCommand: 'pip install sentence-transformers',
                modelPath: '/models/jina-reranker',
                documentationPath: 'docs/architecture/CAPABILITY_PLATFORM_VISION.md#reranking',
            ),
        ];
    }

    public static function find(string $engineId): ?EngineProvisionSpec
    {
        foreach (self::all() as $spec) {
            if ($spec->engineId === $engineId) {
                return $spec;
            }
        }

        return null;
    }
}
