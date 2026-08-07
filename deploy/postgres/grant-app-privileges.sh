#!/usr/bin/env bash
set -Eeuo pipefail

: "${PGHOST:?PGHOST belum diset}"
: "${PGDATABASE:?PGDATABASE belum diset}"
: "${PGUSER:?PGUSER owner belum diset}"
: "${APP_DB_ROLE:?APP_DB_ROLE belum diset}"

psql \
    --dbname "$PGDATABASE" \
    --set=ON_ERROR_STOP=1 \
    --set=app_role="$APP_DB_ROLE" <<'SQL'
SELECT format('GRANT USAGE ON SCHEMA public TO %I', :'app_role')\gexec
SELECT format('GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO %I', :'app_role')\gexec
SELECT format('GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO %I', :'app_role')\gexec
SELECT format('ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO %I', :'app_role')\gexec
SELECT format('ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO %I', :'app_role')\gexec

-- Audit remains append-only for the application role, including after the
-- broad grants above. The owner role can still perform controlled migrations.
SELECT format('REVOKE UPDATE, DELETE ON TABLE audit_logs FROM %I', :'app_role')
WHERE to_regclass('public.audit_logs') IS NOT NULL\gexec
SQL

echo "Privilege aplikasi diperbarui untuk role ${APP_DB_ROLE}."
