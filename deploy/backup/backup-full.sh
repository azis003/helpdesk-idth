#!/usr/bin/env bash
set -Eeuo pipefail

umask 077

: "${PGHOST:?PGHOST belum diset}"
: "${PGDATABASE:?PGDATABASE belum diset}"
: "${PGUSER:?PGUSER belum diset}"

BACKUP_ROOT="${BACKUP_ROOT:-/var/backups/sihati}"
BACKUP_STATUS_FILE="${BACKUP_STATUS_FILE:-${BACKUP_ROOT}/backup-status.json}"
FULL_BACKUP_RETENTION_DAYS="${FULL_BACKUP_RETENTION_DAYS:-35}"
BACKUP_S3_URI="${BACKUP_S3_URI:-}"

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=./status.sh
source "${SCRIPT_DIR}/status.sh"

command -v pg_basebackup >/dev/null 2>&1 || { echo "pg_basebackup tidak ditemukan." >&2; exit 1; }
command -v sha256sum >/dev/null 2>&1 || { echo "sha256sum tidak ditemukan." >&2; exit 1; }
command -v tar >/dev/null 2>&1 || { echo "tar tidak ditemukan." >&2; exit 1; }

timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
completed_at="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
destination_dir="${BACKUP_ROOT}/full"
destination="${destination_dir}/sihati-base-${timestamp}.tar.gz"

mkdir -p -- "$destination_dir"
temporary_dir="$(mktemp -d "${BACKUP_ROOT}/.base-${timestamp}.XXXXXX")"
temporary_archive="${destination}.tmp"
trap 'rm -rf -- "$temporary_dir" "$temporary_archive"' EXIT

pg_basebackup \
    --format=plain \
    --wal-method=stream \
    --checkpoint=fast \
    --no-password \
    --pgdata="$temporary_dir" \
    --progress

tar --create --gzip --file="$temporary_archive" --directory="$temporary_dir" .
mv -- "$temporary_archive" "$destination"
sha256sum "$destination" > "${destination}.sha256"

if [[ -n "$BACKUP_S3_URI" ]]; then
    command -v aws >/dev/null 2>&1 || { echo "aws CLI diperlukan untuk BACKUP_S3_URI." >&2; exit 1; }
    aws s3 cp "$destination" "${BACKUP_S3_URI%/}/full/$(basename -- "$destination")" --sse AES256
    aws s3 cp "${destination}.sha256" "${BACKUP_S3_URI%/}/full/$(basename -- "${destination}.sha256")" --sse AES256
fi

find "$destination_dir" -type f -mtime "+${FULL_BACKUP_RETENTION_DAYS}" -delete
update_backup_status full_backup_completed_at "$completed_at"

echo "Backup PostgreSQL penuh selesai: ${destination}"
