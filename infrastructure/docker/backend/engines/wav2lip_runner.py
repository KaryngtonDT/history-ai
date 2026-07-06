#!/usr/bin/env python3
"""Lumen Wav2Lip CLI — lip sync with Lumen JSON contract."""
from __future__ import annotations

import argparse
import json
import os
import subprocess
import sys


def main() -> int:
    parser = argparse.ArgumentParser(description="Lumen Wav2Lip lip sync")
    parser.add_argument("--video", required=True)
    parser.add_argument("--audio", required=True)
    parser.add_argument("--model", default="wav2lip")
    parser.add_argument("--base-path", default="/models/wav2lip")
    parser.add_argument("--output", required=True)
    parser.add_argument("--audio-duration", type=float, default=3.0)
    args = parser.parse_args()

    src = os.environ.get("WAV2LIP_SRC", "/models/src/Wav2Lip")
    checkpoint = os.path.join(args.base_path, "wav2lip_gan.pth")
    if not os.path.isfile(checkpoint):
        print(f"Missing checkpoint: {checkpoint}", file=sys.stderr)
        return 1

    os.makedirs(os.path.dirname(args.output) or ".", exist_ok=True)
    cmd = [
        sys.executable,
        os.path.join(src, "inference.py"),
        "--checkpoint_path",
        checkpoint,
        "--face",
        args.video,
        "--audio",
        args.audio,
        "--outfile",
        args.output,
    ]
    proc = subprocess.run(cmd, cwd=src, capture_output=True, text=True)
    if proc.returncode != 0:
        print(proc.stderr or proc.stdout, file=sys.stderr)
        return proc.returncode

    print(json.dumps({"duration": max(1.0, args.audio_duration), "output": args.output}))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
