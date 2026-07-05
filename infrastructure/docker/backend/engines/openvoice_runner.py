#!/usr/bin/env python3
"""Lumen OpenVoice V2 CLI — tone-color conversion with Lumen JSON contract."""
from __future__ import annotations

import argparse
import json
import os
import sys
import wave


def _duration(path: str) -> float:
    with wave.open(path, "rb") as handle:
        return handle.getnframes() / float(handle.getframerate())


def _device() -> str:
    try:
        import torch

        return "cuda" if torch.cuda.is_available() else "cpu"
    except Exception:
        return "cpu"


def main() -> int:
    parser = argparse.ArgumentParser(description="Lumen OpenVoice voice clone")
    parser.add_argument("--reference", required=True)
    parser.add_argument("--source", required=True)
    parser.add_argument("--text", required=True)
    parser.add_argument("--model", default="openvoice_v2")
    parser.add_argument("--base-path", default="/models/openvoice")
    parser.add_argument("--output", required=True)
    parser.add_argument("--source-duration", type=float, default=3.0)
    args = parser.parse_args()

    ckpt = os.path.join(args.base_path, "checkpoints_v2")
    if not os.path.isdir(ckpt):
        print(f"Missing checkpoints_v2 under {args.base_path}", file=sys.stderr)
        return 1

    os.makedirs(os.path.dirname(args.output) or ".", exist_ok=True)

    device = _device()
    try:
        from openvoice.api import ToneColorConverter
        from openvoice import se_extractor
        from melo.api import TTS
    except ImportError as exc:
        print(f"OpenVoice stack not installed: {exc}", file=sys.stderr)
        return 1

    converter = ToneColorConverter(f"{ckpt}/converter/config.json", device=device)
    converter.load_ckpt(f"{ckpt}/converter/checkpoint.pth")

    target_se, _ = se_extractor.get_se(args.reference, converter, vad=True)

    tts = TTS(language="EN", device=device)
    speaker_id = tts.hps.data.spk2id["EN-US"]
    tmp_src = args.output + ".src.wav"
    tts.tts_to_file(args.text, speaker_id, tmp_src, speed=1.0)

    converter.convert(
        audio_src_path=tmp_src,
        src_se=se_extractor.get_se(tmp_src, converter, vad=True)[0],
        tgt_se=target_se,
        output_path=args.output,
    )

    if os.path.isfile(tmp_src):
        os.remove(tmp_src)

    payload = {
        "duration": _duration(args.output),
        "sampleRate": 44100,
        "output": args.output,
    }
    print(json.dumps(payload))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
