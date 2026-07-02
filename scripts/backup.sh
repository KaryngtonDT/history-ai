#!/usr/bin/env bash
# Lumen backup — PostgreSQL dump, storage volume, shadow/learning JSON, configuration.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP_DIR="${ROOT}/backups/lumen-${TIMESTAMP}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.yml}"

mkdir -p "${BACKUP_DIR}/postgres" "${BACKUP_DIR}/storage" "${BACKUP_DIR}/configuration"

echo "==> Backup directory: ${BACKUP_DIR}"

if docker compose -f "${ROOT}/${COMPOSE_FILE}" ps postgres --status running >/dev/null 2>&1; then
  echo "==> Dumping PostgreSQL..."
  docker compose -f "${ROOT}/${COMPOSE_FILE}" exec -T postgres \
    pg_dump -U "${POSTGRES_USER:-history_ai}" "${POSTGRES_DB:-history_ai}" \
    | gzip > "${BACKUP_DIR}/postgres/history_ai.sql.gz"
else
  echo "WARN: postgres container not running — skipping database dump"
fi

echo "==> Archiving storage volume from backend..."
docker compose -f "${ROOT}/${COMPOSE_FILE}" exec -T backend \
  tar -czf - -C /var/www/html storage \
  > "${BACKUP_DIR}/storage/storage.tar.gz"

echo "==> Copying configuration..."
cp "${ROOT}/.env.example" "${BACKUP_DIR}/configuration/env.example" 2>/dev/null || true
if [[ -f "${ROOT}/.env" ]]; then
  cp "${ROOT}/.env" "${BACKUP_DIR}/configuration/env"
fi
cp "${ROOT}/docker-compose.yml" "${BACKUP_DIR}/configuration/"
cp "${ROOT}/docker-compose.prod-like.yml" "${BACKUP_DIR}/configuration/"

echo "==> Writing manifest..."
cat > "${BACKUP_DIR}/manifest.json" <<EOF
{
  "createdAt": "$(date -Iseconds)",
  "postgres": "postgres/history_ai.sql.gz",
  "storage": "storage/storage.tar.gz",
  "configuration": "configuration/"
}
EOF

if command -v sha256sum >/dev/null 2>&1; then
  (cd "${BACKUP_DIR}" && find . -type f ! -name 'checksums.sha256' -print0 | sort -z | xargs -0 sha256sum) \
    > "${BACKUP_DIR}/checksums.sha256"
elif command -v shasum >/dev/null 2>&1; then
  (cd "${BACKUP_DIR}" && find . -type f ! -name 'checksums.sha256' -print0 | sort -z | xargs -0 shasum -a 256) \
    > "${BACKUP_DIR}/checksums.sha256"
fi

ln -sfn "${BACKUP_DIR}" "${ROOT}/backups/latest"
echo "==> Backup complete: ${BACKUP_DIR}"
