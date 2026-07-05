#!/usr/bin/env python3
"""Lumen F5-TTS CLI — wraps upstream f5-tts_infer-cli with Lumen contract."""
from __future__ import annotations

import argparse
import json
import os
import shutil
import subprocess
import sys
import tempfile
import wave


def _duration(path: str) -> float:
    with wave.open(path, "rb") as handle:
        return handle.getnframes() / float(handle.getframerate())


def _resolve_ref(base_path: str, voice: str) -> tuple[str, str]:
    refs = os.path.join(base_path, "refs")
    for name in (f"{voice}.wav", "default.wav"):
        candidate = os.path.join(refs, name)
        if os.path.isfile(candidate):
            text_path = candidate + ".txt"
            ref_text = ""
            if os.path.isfile(text_path):
                with open(text_path, encoding="utf-8") as handle:
                    ref_text = handle.read().strip()
            return candidate, ref_text
    raise FileNotFoundError(f"No reference wav in {refs} (expected default.wav)")


def main() -> int:
    parser = argparse.ArgumentParser(description="Lumen F5-TTS synthesis")
    parser.add_argument("--text", required=True)
    parser.add_argument("--voice", default="default")
    parser.add_argument("--model", default="F5-TTS")
    parser.add_argument("--base-path", default="/models/f5")
    parser.add_argument("--output", required=True)
    args = parser.parse_args()

    try:
        ref_audio, ref_text = _resolve_ref(args.base_path, args.voice)
    except FileNotFoundError as exc:
        print(str(exc), file=sys.stderr)
        return 1

    os.makedirs(os.path.dirname(args.output) or ".", exist_ok=True)

    venv_cli = "/models/venvs/f5-tts/bin/f5-tts_infer-cli"
    cli = venv_cli if os.path.isfile(venv_cli) else "f5-tts_infer-cli"

    with tempfile.TemporaryDirectory() as tmp:
        tmp_out = os.path.join(tmp, "out.wav")
        cmd = [
            cli,
            "--model",
            "F5TTS_v1_Base",
            "--ref_audio",
            ref_audio,
            "--ref_text",
            ref_text,
            "--gen_text",
            args.text,
            "--output_dir",
            tmp,
        ]
        result = subprocess.run(cmd, capture_output=True, text=True)
        if result.returncode != 0:
            print(result.stderr or result.stdout, file=sys.stderr)
            return result.returncode

        produced = tmp_out
        if not os.path.isfile(produced):
            wavs = [f for f in os.listdir(tmp) if f.endswith(".wav")]
            if not wavs:
                print("F5-TTS produced no wav output", file=sys.stderr)
                return 1
            produced = os.path.join(tmp, wavs[0])

        shutil.copyfile(produced, args.output)

    payload = {
        "duration": _duration(args.output),
        "format": "wav",
        "output": args.output,
    }
    print(json.dumps(payload))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
