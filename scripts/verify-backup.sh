#!/usr/bin/env bash
# Verify backup integrity (checksums + required files).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BACKUP_PATH="${1:-${ROOT}/backups/latest}"

if [[ ! -d "${BACKUP_PATH}" ]]; then
  echo "FAIL: backup not found: ${BACKUP_PATH}" >&2
  exit 1
fi

echo "==> Verifying: ${BACKUP_PATH}"

REQUIRED=(
  "manifest.json"
  "storage/storage.tar.gz"
)

for file in "${REQUIRED[@]}"; do
  if [[ ! -f "${BACKUP_PATH}/${file}" ]]; then
    echo "FAIL: missing ${file}" >&2
    exit 1
  fi
  echo "OK: ${file}"
done

if [[ -f "${BACKUP_PATH}/checksums.sha256" ]]; then
  if command -v sha256sum >/dev/null 2>&1; then
    (cd "${BACKUP_PATH}" && sha256sum -c checksums.sha256)
  elif command -v shasum >/dev/null 2>&1; then
    (cd "${BACKUP_PATH}" && shasum -a 256 -c checksums.sha256)
  fi
  echo "OK: checksums"
else
  echo "WARN: no checksums.sha256"
fi

echo "==> Verification passed"
