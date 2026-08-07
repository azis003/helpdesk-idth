#!/usr/bin/env bash
set -Eeuo pipefail

# Collect a reproducible technical verification bundle. The script deliberately
# treats missing staging evidence as a deviation so a local run cannot be
# mistaken for a production go-live approval.

umask 077

timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
evidence_root="${GO_LIVE_EVIDENCE_DIR:-storage/app/private/evidence/go-live/${timestamp}}"
logs_dir="${evidence_root}/logs"
manifest_file="${evidence_root}/manifest.tsv"
summary_file="${evidence_root}/summary.md"
php_bin="${PHP_BIN:-php}"
composer_bin="${COMPOSER_BIN:-composer}"

mkdir -p -- "$logs_dir"
printf 'id\tstatus\tstarted_at\tfinished_at\tduration_seconds\tevidence\tnote\n' > "$manifest_file"

overall=0

now() {
    date -u +%Y-%m-%dT%H:%M:%SZ
}

safe_name() {
    printf '%s' "$1" | tr '[:upper:]' '[:lower:]' | tr -cs '[:alnum:]_.-' '_'
}

record() {
    local id="$1"
    local status="$2"
    local started_at="$3"
    local finished_at="$4"
    local duration="$5"
    local evidence="$6"
    local note="$7"

    evidence="${evidence:--}"
    note="${note//$'\t'/ }"
    note="${note//$'\n'/ }"
    printf '%s\t%s\t%s\t%s\t%s\t%s\t%s\n' \
        "$id" "$status" "$started_at" "$finished_at" "$duration" "$evidence" "$note" >> "$manifest_file"

    case "$status" in
        Gagal) overall=1 ;;
        Deviasi)
            if [[ "$overall" -eq 0 ]]; then
                overall=2
            fi
            ;;
    esac

    return 0
}

run_check() {
    local id="$1"
    local description="$2"
    shift 2

    local log_file="${logs_dir}/$(safe_name "$id").log"
    local started_epoch="$(date +%s)"
    local started_at="$(now)"
    local finished_at
    local duration
    local status
    local note

    if "$@" >"$log_file" 2>&1; then
        status="Lulus"
        note="$description"
    else
        status="Gagal"
        note="$description; periksa log command."
    fi

    finished_epoch="$(date +%s)"
    finished_at="$(now)"
    duration=$((finished_epoch - started_epoch))
    record "$id" "$status" "$started_at" "$finished_at" "$duration" "$log_file" "$note"
}

record_deviation() {
    local id="$1"
    local note="$2"
    local started_at="$(now)"
    record "$id" "Deviasi" "$started_at" "$started_at" "0" "" "$note"
}

record_value_check() {
    local id="$1"
    local description="$2"
    local value="$3"
    local expected="$4"
    local started_at="$(now)"

    if [[ "$value" == "$expected" ]]; then
        record "$id" "Lulus" "$started_at" "$started_at" "0" "" "$description"
    else
        record "$id" "Deviasi" "$started_at" "$started_at" "0" "" \
            "$description; nilai aktual '${value:-<kosong>}', ekspektasi '${expected}'."
    fi
}

run_health_check() {
    local id="ops-health"
    local log_file="${logs_dir}/ops-health.json"
    local stderr_file="${logs_dir}/ops-health.stderr.log"
    local started_epoch="$(date +%s)"
    local started_at="$(now)"
    local finished_at
    local duration

    if "$php_bin" artisan sihati:ops:health --json >"$log_file" 2>"$stderr_file" \
        && "$php_bin" -r '$payload = json_decode((string) file_get_contents($argv[1]), true); exit(is_array($payload) && ($payload["ready"] ?? false) === true && ($payload["status"] ?? "") === "healthy" ? 0 : 1);' "$log_file"; then
        record "$id" "Lulus" "$started_at" "$(now)" "$(( $(date +%s) - started_epoch ))" "$log_file" \
            "Readiness sehat; stderr: ${stderr_file}."
    else
        finished_at="$(now)"
        duration=$(( $(date +%s) - started_epoch ))
        if [[ -s "$log_file" ]]; then
            record "$id" "Deviasi" "$started_at" "$finished_at" "$duration" "$log_file" \
                "Health belum healthy/ready; go-live ditahan. stderr: ${stderr_file}."
        else
            record "$id" "Gagal" "$started_at" "$finished_at" "$duration" "$stderr_file" \
                "Perintah health gagal dijalankan."
        fi
    fi
}

validate_performance_evidence() {
    local summary="${GO_LIVE_PERFORMANCE_SUMMARY:-}"
    local metadata="${GO_LIVE_PERFORMANCE_METADATA:-}"

    [[ -f "$summary" && -f "$metadata" ]] || return 1

    "$php_bin" -r '
        $summary = json_decode((string) file_get_contents($argv[1]), true);
        $metadata = json_decode((string) file_get_contents($argv[2]), true);
        if (!is_array($summary) || !is_array($metadata)) { exit(1); }
        $metrics = $summary["metrics"] ?? [];
        $thresholds = [
            "ticket_list_duration" => "p(95)<2000",
            "ticket_create_duration" => "p(95)<1000",
            "checks" => "rate>0.99",
        ];
        foreach ($thresholds as $metric => $threshold) {
            if (($metrics[$metric]["thresholds"][$threshold]["ok"] ?? false) !== true) { exit(1); }
        }
        if (($metadata["environment"] ?? "") !== "staging"
            || (int) ($metadata["active_ticket_count"] ?? 0) < 300
            || (int) ($metadata["vus"] ?? 0) < 30
            || (float) ($metadata["error_rate"] ?? 1) >= 0.01
            || ! isset($metadata["base_url"], $metadata["commit"], $metadata["tested_at"])) { exit(1); }
    ' "$summary" "$metadata"
}

validate_restore_evidence() {
    local evidence="${GO_LIVE_RESTORE_EVIDENCE:-}"

    [[ -f "$evidence" ]] || return 1
    "$php_bin" -r '
        $payload = json_decode((string) file_get_contents($argv[1]), true);
        exit(is_array($payload)
            && ($payload["result"] ?? "") === "passed"
            && ($payload["rpo_passed"] ?? false) === true
            && ($payload["rto_passed"] ?? false) === true
            && (int) ($payload["rpo_seconds"] ?? PHP_INT_MAX) <= 3600
            && (int) ($payload["duration_seconds"] ?? PHP_INT_MAX) <= 14400
            ? 0 : 1);
    ' "$evidence"
}

validate_master_data_evidence() {
    local evidence="${GO_LIVE_MASTER_DATA_EVIDENCE:-}"

    [[ -f "$evidence" ]] || return 1
    "$php_bin" -r '
        $payload = json_decode((string) file_get_contents($argv[1]), true);
        exit(is_array($payload)
            && ($payload["environment"] ?? "") === "staging"
            && ($payload["approved"] ?? false) === true
            && (int) ($payload["users"] ?? 0) >= 150
            && (int) ($payload["teams"] ?? 0) >= 6
            && (int) ($payload["services"] ?? 0) >= 7
            ? 0 : 1);
    ' "$evidence"
}

validate_signoff_evidence() {
    local evidence="${GO_LIVE_SIGNOFF_FILE:-}"

    [[ -f "$evidence" ]] || return 1
    "$php_bin" -r '
        $payload = json_decode((string) file_get_contents($argv[1]), true);
        $required = ["product", "operations", "infrastructure", "security"];
        if (!is_array($payload)
            || ($payload["environment"] ?? "") !== "staging"
            || ($payload["decision"] ?? "") !== "Go") { exit(1); }
        foreach ($required as $role) {
            if (($payload["approvals"][$role] ?? false) !== true) { exit(1); }
        }
        exit(0);
    ' "$evidence"
}

# Static environment controls are intentionally checked before any go-live
# evidence is accepted. Export .env.production or the approved staging env
# into the shell before running this script.
record_value_check "env-app-debug" "APP_DEBUG harus false." "${APP_DEBUG:-}" "false"
if [[ "${APP_URL:-}" == https://* ]]; then
    record "env-app-url-https" "Lulus" "$(now)" "$(now)" "0" "" "APP_URL memakai HTTPS."
else
    record_deviation "env-app-url-https" "APP_URL belum berupa URL HTTPS staging/production."
fi
record_value_check "env-session-cookie" "SESSION_SECURE_COOKIE harus true." "${SESSION_SECURE_COOKIE:-}" "true"
record_value_check "env-queue" "QUEUE_CONNECTION production harus redis." "${QUEUE_CONNECTION:-}" "redis"
record_value_check "env-cache" "CACHE_STORE production harus redis." "${CACHE_STORE:-}" "redis"
if [[ -n "${ATTACHMENT_PRIVATE_DISK:-}" && "${ATTACHMENT_PRIVATE_DISK}" != "public" ]]; then
    record "env-private-storage" "Lulus" "$(now)" "$(now)" "0" "" "Disk lampiran bukan public."
else
    record_deviation "env-private-storage" "ATTACHMENT_PRIVATE_DISK harus berupa disk private dan tidak boleh public."
fi
record_value_check "env-require-scheduler" "Readiness harus mewajibkan heartbeat scheduler." "${OPS_REQUIRE_SCHEDULER_HEARTBEAT:-}" "true"
record_value_check "env-require-backup" "Readiness harus mewajibkan status backup." "${OPS_REQUIRE_BACKUP_STATUS:-}" "true"
record_value_check "env-require-storage" "Readiness harus mewajibkan storage check." "${OPS_REQUIRE_STORAGE_CHECK:-}" "true"

if [[ "${APP_ENV:-}" == "staging" || "${APP_ENV:-}" == "production" ]]; then
    record "env-approved-environment" "Lulus" "$(now)" "$(now)" "0" "" "APP_ENV berada pada staging/production."
else
    record_deviation "env-approved-environment" "APP_ENV bukan staging/production; hasil ini tidak dapat menjadi bukti go-live."
fi

run_check "composer-validate" "Manifest Composer tervalidasi." "$composer_bin" validate --strict
run_check "formatting" "Pint tidak menemukan perubahan format." "$php_bin" vendor/bin/pint --test
run_check "preflight-clear-cache" "Cache runtime dibersihkan sebelum test." "$php_bin" artisan optimize:clear
if [[ -n "${GO_LIVE_TEST_DATABASE:-}" && "${GO_LIVE_TEST_DATABASE}" != "${DB_DATABASE:-}" && "${GO_LIVE_TEST_DATABASE}" != "sihati" ]]; then
    run_check "phpunit" "Feature dan unit test lulus pada database uji terisolasi." \
        env APP_ENV=testing DB_DATABASE="$GO_LIVE_TEST_DATABASE" \
        "$php_bin" vendor/bin/phpunit --log-junit="${evidence_root}/phpunit.xml"
else
    record_deviation "phpunit" "GO_LIVE_TEST_DATABASE wajib menunjuk database uji terisolasi dan tidak boleh sama dengan DB_DATABASE aplikasi."
fi
run_check "npm-install" "Dependency frontend terpasang dari lockfile." npm ci --no-audit --no-fund
run_check "frontend-build" "Asset frontend berhasil dibangun." npm run build
run_check "config-cache" "Cache konfigurasi berhasil dibuat." "$php_bin" artisan config:cache
run_check "route-cache" "Cache route berhasil dibuat." "$php_bin" artisan route:cache
run_check "view-cache" "Cache Blade berhasil dibuat." "$php_bin" artisan view:cache
run_check "migration-status" "Status migrasi database dapat dibaca." "$php_bin" artisan migrate:status
run_health_check
run_check "storage-health" "Pemeriksaan kapasitas private storage berhasil." "$php_bin" artisan sihati:ops:check-storage --json
run_check "schedule-list" "Daftar scheduler dapat dibaca." "$php_bin" artisan schedule:list

if validate_master_data_evidence; then
    record "master-data" "Lulus" "$(now)" "$(now)" "0" "${GO_LIVE_MASTER_DATA_EVIDENCE}" \
        "Data master staging disahkan dan memenuhi minimum PRD."
else
    record_deviation "master-data" "Bukti data master staging belum tersedia atau belum memenuhi minimum PRD (150 user, 6 tim, 7 layanan)."
fi

if validate_performance_evidence; then
    record "performance" "Lulus" "$(now)" "$(now)" "0" "${GO_LIVE_PERFORMANCE_SUMMARY};${GO_LIVE_PERFORMANCE_METADATA}" \
        "P95, error rate, 300 tiket aktif, dan 30 VU memenuhi target."
else
    record_deviation "performance" "Bukti k6 staging belum tersedia atau threshold P95/error rate/data volume belum terpenuhi."
fi

if validate_restore_evidence; then
    record "restore-drill" "Lulus" "$(now)" "$(now)" "0" "${GO_LIVE_RESTORE_EVIDENCE}" \
        "Restore database dan private storage memenuhi RPO <= 1 jam dan RTO <= 4 jam."
else
    record_deviation "restore-drill" "Bukti restore drill staging belum tersedia atau RPO/RTO belum memenuhi target."
fi

if validate_signoff_evidence; then
    record "sign-off" "Lulus" "$(now)" "$(now)" "0" "$GO_LIVE_SIGNOFF_FILE" \
        "Berkas sign-off staging menyatakan Go dan memiliki empat approval wajib."
else
    record_deviation "sign-off" "Berkas sign-off staging belum tersedia, belum menyatakan Go, atau belum memiliki empat approval wajib."
fi

{
    echo "# Technical verification evidence"
    echo
    echo "- Generated at (UTC): ${timestamp}"
    echo "- Environment: ${APP_ENV:-<unset>}"
    echo "- Exit code: ${overall} (0=Lulus, 1=Gagal, 2=Deviasi/no-go)"
    echo
    echo "| ID | Status | Started | Finished | Duration (s) | Evidence | Note |"
    echo "|---|---|---|---|---:|---|---|"
    tail -n +2 "$manifest_file" | while IFS=$'\t' read -r id status started finished duration evidence note; do
        echo "| ${id} | ${status} | ${started} | ${finished} | ${duration} | ${evidence:--} | ${note} |"
    done
    echo
    echo "Arsipkan direktori evidence ini bersama commit/image tag, konfigurasi environment tanpa secret, data master approval, hasil k6, restore evidence, dan sign-off."
} > "$summary_file"

echo "Evidence go-live tersimpan di ${evidence_root}"
echo "Ringkasan: ${summary_file}"

exit "$overall"
