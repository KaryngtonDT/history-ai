#!/usr/bin/env bash
# Pull default Ollama model and verify pipeline engine binaries.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
exec bash "${ROOT}/scripts/provision-engines.sh"
