#!/usr/bin/env bash
# Lumen restore — from a backup created by scripts/backup.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BACKUP_PATH="${1:-${ROOT}/backups/latest}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.yml}"

if [[ ! -d "${BACKUP_PATH}" ]]; then
  echo "ERROR: backup not found: ${BACKUP_PATH}" >&2
  exit 1
fi

echo "==> Restoring from: ${BACKUP_PATH}"

if [[ -f "${BACKUP_PATH}/storage/storage.tar.gz" ]]; then
  echo "==> Restoring storage volume via backend..."
  gunzip -c "${BACKUP_PATH}/storage/storage.tar.gz" | \
    docker compose -f "${ROOT}/${COMPOSE_FILE}" exec -T backend \
      tar -xzf - -C /var/www/html
else
  echo "WARN: storage archive missing"
fi

if [[ -f "${BACKUP_PATH}/postgres/history_ai.sql.gz" ]]; then
  echo "==> Restoring PostgreSQL..."
  gunzip -c "${BACKUP_PATH}/postgres/history_ai.sql.gz" | \
    docker compose -f "${ROOT}/${COMPOSE_FILE}" exec -T postgres \
      psql -U "${POSTGRES_USER:-history_ai}" -d "${POSTGRES_DB:-history_ai}"
else
  echo "WARN: postgres dump missing"
fi

echo "==> Restore complete. Restart services: make prod-restart"
