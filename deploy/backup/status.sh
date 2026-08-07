#!/usr/bin/env bash

# Shared helper for the backup jobs. jq is intentionally required so a
# partial job cannot silently replace a healthy WAL timestamp with an empty
# value.

update_backup_status() {
    local field="$1"
    local value="$2"
    local status_file="${BACKUP_STATUS_FILE:?BACKUP_STATUS_FILE belum diset}"
    local directory
    local temporary

    command -v jq >/dev/null 2>&1 || {
        echo "jq wajib tersedia untuk memperbarui status backup." >&2
        return 1
    }

    directory="$(dirname -- "$status_file")"
    mkdir -p -- "$directory"
    temporary="${status_file}.tmp.$$"

    if [[ -s "$status_file" ]]; then
        jq --arg field "$field" --arg value "$value" \
            '.[$field] = $value | .updated_at = $value' \
            "$status_file" > "$temporary"
    else
        jq -n --arg field "$field" --arg value "$value" \
            '{($field): $value, updated_at: $value}' > "$temporary"
    fi

    chmod 600 "$temporary"
    mv -- "$temporary" "$status_file"
}
