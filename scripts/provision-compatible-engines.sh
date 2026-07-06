#!/usr/bin/env bash
# Intelligent provisioning — uses cached hardware report, installs compatible engines only.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT}"

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod-like.yml}"
COMPOSE=(docker compose -f "${COMPOSE_FILE}")
BACKEND_URL="${BACKEND_URL:-http://localhost:8000}"

echo "========================================================="
echo "   LUMEN — INTELLIGENT ENGINE PROVISIONING"
echo "========================================================="

echo ""
echo "[1/4] Ensuring stack is up..."
"${COMPOSE[@]}" up -d postgres redis ollama backend >/dev/null

echo ""
echo "[2/4] Refreshing hardware capability report (one-time source of truth)..."
curl -sf "${BACKEND_URL}/api/runtime/hardware" >/dev/null \
  || { echo "Backend not reachable at ${BACKEND_URL}"; exit 1; }

echo ""
echo "[3/4] Showing provisioning plan..."
curl -sf "${BACKEND_URL}/api/runtime/provision/plan" | python3 -m json.tool 2>/dev/null || true

echo ""
echo "[4/4] Provisioning compatible engines only..."
curl -sf -X POST "${BACKEND_URL}/api/runtime/provision/compatible" | python3 -m json.tool 2>/dev/null || true

echo ""
echo "Done. Report: docs/reports/Engine-Provisioning-Final.md"
echo "Docs: docs/operations/ENGINE_PROVISIONING.md"
echo "========================================================="
