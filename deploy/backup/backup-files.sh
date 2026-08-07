#!/usr/bin/env bash
set -Eeuo pipefail

umask 077

: "${PRIVATE_STORAGE_ROOT:?PRIVATE_STORAGE_ROOT belum diset}"

BACKUP_ROOT="${BACKUP_ROOT:-/var/backups/sihati}"
BACKUP_STATUS_FILE="${BACKUP_STATUS_FILE:-${BACKUP_ROOT}/backup-status.json}"
FILE_BACKUP_RETENTION_DAYS="${FILE_BACKUP_RETENTION_DAYS:-35}"
BACKUP_S3_URI="${BACKUP_S3_URI:-}"

[[ -d "$PRIVATE_STORAGE_ROOT" ]] || { echo "Private storage tidak ditemukan: ${PRIVATE_STORAGE_ROOT}" >&2; exit 1; }

timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
destination_dir="${BACKUP_ROOT}/files"
destination="${destination_dir}/sihati-private-${timestamp}.tar.gz"

mkdir -p -- "$destination_dir"
temporary="${destination}.tmp"
trap 'rm -f -- "$temporary"' EXIT

tar --create --gzip --file="$temporary" --directory="$PRIVATE_STORAGE_ROOT" .
mv -- "$temporary" "$destination"
sha256sum "$destination" > "${destination}.sha256"

if [[ -n "$BACKUP_S3_URI" ]]; then
    command -v aws >/dev/null 2>&1 || { echo "aws CLI diperlukan untuk BACKUP_S3_URI." >&2; exit 1; }
    aws s3 cp "$destination" "${BACKUP_S3_URI%/}/files/$(basename -- "$destination")" --sse AES256
    aws s3 cp "${destination}.sha256" "${BACKUP_S3_URI%/}/files/$(basename -- "${destination}.sha256")" --sse AES256
fi

find "$destination_dir" -type f -mtime "+${FILE_BACKUP_RETENTION_DAYS}" -delete

echo "Backup private storage selesai: ${destination}"
