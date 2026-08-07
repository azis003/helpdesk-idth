#!/usr/bin/env bash
set -Eeuo pipefail

: "${APP_ENV:?APP_ENV belum diset}"
[[ "$APP_ENV" == production ]] || { echo "deploy.sh hanya boleh dijalankan dengan APP_ENV=production." >&2; exit 1; }

php artisan down --render=errors::503 --retry=60
cleanup() {
    php artisan up || true
}
trap cleanup EXIT

php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan sihati:ops:health --json

echo "Deployment SIHATI selesai dan health check lulus."
