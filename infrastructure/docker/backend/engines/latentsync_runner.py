#!/usr/bin/env python3
"""Lumen LatentSync CLI — wraps upstream inference with Lumen JSON contract."""
from __future__ import annotations

import argparse
import json
import os
import subprocess
import sys


def _probe_duration(path: str) -> float:
    cmd = [
        "ffprobe",
        "-v",
        "error",
        "-show_entries",
        "format=duration",
        "-of",
        "default=noprint_wrappers=1:nokey=1",
        path,
    ]
    result = subprocess.run(cmd, capture_output=True, text=True)
    if result.returncode == 0 and result.stdout.strip():
        return float(result.stdout.strip())
    return 1.0


def main() -> int:
    parser = argparse.ArgumentParser(description="Lumen LatentSync lip sync")
    parser.add_argument("--video", required=True)
    parser.add_argument("--audio", required=True)
    parser.add_argument("--model", default="latentsync")
    parser.add_argument("--base-path", default="/models/latentsync")
    parser.add_argument("--output", required=True)
    parser.add_argument("--audio-duration", type=float, default=3.0)
    args = parser.parse_args()

    repo = os.environ.get("LATENTSYNC_SRC", "/models/src/LatentSync")
    ckpt = os.path.join(args.base_path, "checkpoints", "latentsync_unet.pt")
    if not os.path.isfile(ckpt):
        print(f"Missing checkpoint {ckpt}", file=sys.stderr)
        return 1

    if not os.path.isdir(repo):
        print(f"LatentSync source not found at {repo}", file=sys.stderr)
        return 1

    os.makedirs(os.path.dirname(args.output) or ".", exist_ok=True)

    cmd = [
        sys.executable,
        "-m",
        "scripts.inference",
        "--unet_config_path",
        "configs/unet/second_stage.yaml",
        "--inference_ckpt_path",
        ckpt,
        "--inference_steps",
        "20",
        "--guidance_scale",
        "1.5",
        "--video_path",
        args.video,
        "--audio_path",
        args.audio,
        "--video_out_path",
        args.output,
    ]
    result = subprocess.run(cmd, cwd=repo, capture_output=True, text=True)
    if result.returncode != 0:
        print(result.stderr or result.stdout, file=sys.stderr)
        return result.returncode

    payload = {
        "duration": _probe_duration(args.output),
        "output": args.output,
    }
    print(json.dumps(payload))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
