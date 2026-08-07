#!/usr/bin/env bash
set -Eeuo pipefail

umask 077

: "${RESTORE_BASE_ARCHIVE:?RESTORE_BASE_ARCHIVE belum diset}"
: "${RESTORE_FILES_ARCHIVE:?RESTORE_FILES_ARCHIVE belum diset}"
: "${RESTORE_PGDATA:?RESTORE_PGDATA belum diset}"
: "${RESTORE_STORAGE_ROOT:?RESTORE_STORAGE_ROOT belum diset}"

[[ -f "$RESTORE_BASE_ARCHIVE" ]] || { echo "Physical base backup tidak ditemukan." >&2; exit 1; }
[[ -f "$RESTORE_FILES_ARCHIVE" ]] || { echo "Arsip private storage tidak ditemukan." >&2; exit 1; }
[[ -n "$RESTORE_PGDATA" && "$RESTORE_PGDATA" != "/" && "$RESTORE_PGDATA" == *sihati* ]] || {
    echo "RESTORE_PGDATA harus berupa direktori restore terisolasi yang memuat nama sihati." >&2
    exit 1
}
[[ -n "$RESTORE_STORAGE_ROOT" && "$RESTORE_STORAGE_ROOT" != "/" && "$RESTORE_STORAGE_ROOT" == *sihati* ]] || {
    echo "RESTORE_STORAGE_ROOT harus berupa direktori restore terisolasi yang memuat nama sihati." >&2
    exit 1
}

RESTORE_DATABASE="${RESTORE_DATABASE:-sihati}"
RESTORE_PGPORT="${RESTORE_PGPORT:-55432}"
RESTORE_WAL_ARCHIVE_DIR="${RESTORE_WAL_ARCHIVE_DIR:-}"
RESTORE_EVIDENCE_FILE="${RESTORE_EVIDENCE_FILE:-restore-drill-$(date +%s).json}"

command -v pg_ctl >/dev/null 2>&1 || { echo "pg_ctl tidak ditemukan." >&2; exit 1; }
command -v psql >/dev/null 2>&1 || { echo "psql tidak ditemukan." >&2; exit 1; }
command -v tar >/dev/null 2>&1 || { echo "tar tidak ditemukan." >&2; exit 1; }

started_at="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
started_epoch="$(date +%s)"
archive_epoch="$(stat -c %Y "$RESTORE_BASE_ARCHIVE")"

rm -rf -- "$RESTORE_PGDATA"
mkdir -p -- "$RESTORE_PGDATA"
tar --extract --gzip --file="$RESTORE_BASE_ARCHIVE" --directory="$RESTORE_PGDATA"
chmod 700 "$RESTORE_PGDATA"
rm -f -- "$RESTORE_PGDATA/postmaster.pid"

cat >> "$RESTORE_PGDATA/postgresql.auto.conf" <<EOF
listen_addresses = '127.0.0.1'
port = ${RESTORE_PGPORT}
EOF

if [[ -n "$RESTORE_WAL_ARCHIVE_DIR" ]]; then
    [[ -d "$RESTORE_WAL_ARCHIVE_DIR" ]] || { echo "WAL archive restore tidak ditemukan." >&2; exit 1; }
    cat >> "$RESTORE_PGDATA/postgresql.auto.conf" <<EOF
restore_command = 'cp ${RESTORE_WAL_ARCHIVE_DIR}/%f %p'
EOF
    touch "$RESTORE_PGDATA/recovery.signal"
fi

cleanup() {
    pg_ctl --pgdata="$RESTORE_PGDATA" --mode=fast --timeout=60 stop >/dev/null 2>&1 || true
}
trap cleanup EXIT

pg_ctl --pgdata="$RESTORE_PGDATA" --options="-p ${RESTORE_PGPORT}" --wait start
PGHOST=127.0.0.1 PGPORT="$RESTORE_PGPORT" psql --dbname="$RESTORE_DATABASE" --command='select count(*) from migrations' >/dev/null
ticket_count="$(PGHOST=127.0.0.1 PGPORT="$RESTORE_PGPORT" psql --tuples-only --no-align --dbname="$RESTORE_DATABASE" --command='select count(*) from tickets')"
attachment_count="$(PGHOST=127.0.0.1 PGPORT="$RESTORE_PGPORT" psql --tuples-only --no-align --dbname="$RESTORE_DATABASE" --command='select count(*) from attachments')"
cleanup
trap - EXIT

mkdir -p -- "$RESTORE_STORAGE_ROOT"
find "$RESTORE_STORAGE_ROOT" -mindepth 1 -maxdepth 1 -exec rm -rf -- {} +
tar --extract --gzip --file="$RESTORE_FILES_ARCHIVE" --directory="$RESTORE_STORAGE_ROOT"
file_count="$(find "$RESTORE_STORAGE_ROOT" -type f | wc -l | tr -d ' ')"

finished_at="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
finished_epoch="$(date +%s)"
duration_seconds=$((finished_epoch - started_epoch))

if [[ -n "$RESTORE_WAL_ARCHIVE_DIR" ]]; then
    latest_wal="$(find "$RESTORE_WAL_ARCHIVE_DIR" -type f -printf '%T@\n' | sort -nr | head -n 1 || true)"
else
    latest_wal=""
fi

if [[ -n "$latest_wal" ]]; then
    latest_wal_epoch="${latest_wal%%.*}"
    rpo_seconds=$((started_epoch - latest_wal_epoch))
else
    rpo_seconds=$((started_epoch - archive_epoch))
fi

rpo_passed=false
rto_passed=false
(( rpo_seconds <= 3600 )) && rpo_passed=true
(( duration_seconds <= 14400 )) && rto_passed=true
result="passed"
if [[ "$rpo_passed" != true || "$rto_passed" != true ]]; then
    result="failed"
fi

cat > "$RESTORE_EVIDENCE_FILE" <<EOF
{
  "started_at": "${started_at}",
  "finished_at": "${finished_at}",
  "duration_seconds": ${duration_seconds},
  "rpo_seconds": ${rpo_seconds},
  "rpo_target_seconds": 3600,
  "rpo_passed": ${rpo_passed},
  "rto_target_seconds": 14400,
  "rto_passed": ${rto_passed},
  "database": "${RESTORE_DATABASE}",
  "ticket_count": ${ticket_count//[[:space:]]/},
  "attachment_count": ${attachment_count//[[:space:]]/},
  "restored_file_count": ${file_count},
  "physical_base_backup": "$(basename -- "$RESTORE_BASE_ARCHIVE")",
  "private_storage_archive": "$(basename -- "$RESTORE_FILES_ARCHIVE")",
  "result": "${result}"
}
EOF

if [[ "$result" != passed ]]; then
    echo "Restore drill gagal memenuhi RPO/RTO. Bukti: ${RESTORE_EVIDENCE_FILE}" >&2
    exit 1
fi

echo "Restore drill selesai dalam ${duration_seconds}s dengan RPO ${rpo_seconds}s. Bukti: ${RESTORE_EVIDENCE_FILE}"
