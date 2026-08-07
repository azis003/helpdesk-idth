#!/usr/bin/env bash
set -Eeuo pipefail

: "${APP_DB_ROLE:?APP_DB_ROLE belum diset}"
: "${APP_DB_PASSWORD:?APP_DB_PASSWORD belum diset}"

psql \
    --username "$POSTGRES_USER" \
    --dbname "$POSTGRES_DB" \
    --set=ON_ERROR_STOP=1 \
    --set=app_role="$APP_DB_ROLE" \
    --set=app_password="$APP_DB_PASSWORD" <<'SQL'
SELECT format('CREATE ROLE %I LOGIN PASSWORD %L', :'app_role', :'app_password')
WHERE NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = :'app_role')\gexec

SELECT format('ALTER ROLE %I LOGIN PASSWORD %L', :'app_role', :'app_password')\gexec
SELECT format('GRANT CONNECT ON DATABASE %I TO %I', current_database(), :'app_role')\gexec
SELECT format('GRANT USAGE ON SCHEMA public TO %I', :'app_role')\gexec
SQL
