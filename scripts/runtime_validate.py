#!/usr/bin/env python3
"""Runtime validation — exit 0 when Core Runtime is healthy."""
from __future__ import annotations

import json
import sys
import urllib.request

API = "http://localhost:8000/api/runtime/pipeline/validate"


def main() -> int:
    request = urllib.request.Request(API, data=b"{}", method="POST")
    with urllib.request.urlopen(request) as response:
        payload = json.loads(response.read().decode())

    print(json.dumps(payload, indent=4))

    core = payload.get("coreRuntime") or {}
    if core.get("status") == "ready" or payload.get("status") == "pass":
        return 0

    return 1


if __name__ == "__main__":
    sys.exit(main())
