#!/usr/bin/env bash
set -Eeuo pipefail

umask 077

: "${WAL_ARCHIVE_DIR:?WAL_ARCHIVE_DIR belum diset}"

BACKUP_ROOT="${BACKUP_ROOT:-/var/backups/sihati}"
BACKUP_STATUS_FILE="${BACKUP_STATUS_FILE:-${BACKUP_ROOT}/backup-status.json}"
MAX_WAL_AGE_SECONDS="${MAX_WAL_AGE_SECONDS:-3600}"

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=./status.sh
source "${SCRIPT_DIR}/status.sh"

[[ -d "$WAL_ARCHIVE_DIR" ]] || { echo "Direktori WAL archive tidak ditemukan." >&2; exit 1; }

latest="$(find "$WAL_ARCHIVE_DIR" -type f -printf '%T@ %p\n' | sort -nr | head -n 1 || true)"
[[ -n "$latest" ]] || { echo "Belum ada WAL yang diarsipkan." >&2; exit 1; }

latest_epoch="${latest%%.*}"
now_epoch="$(date +%s)"
age=$((now_epoch - latest_epoch))

if (( age > MAX_WAL_AGE_SECONDS )); then
    echo "WAL archive terlalu lama: ${age}s (maksimum ${MAX_WAL_AGE_SECONDS}s)." >&2
    exit 1
fi

wal_at="$(date -u -d "@${latest_epoch}" +%Y-%m-%dT%H:%M:%SZ)"
update_backup_status wal_archived_at "$wal_at"
echo "WAL archive sehat: ${age}s; terakhir ${wal_at}."
