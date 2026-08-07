SELECT format('GRANT USAGE ON SCHEMA public TO %I', :'app_role')\gexec
SELECT format('GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO %I', :'app_role')\gexec
SELECT format('GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO %I', :'app_role')\gexec
SELECT format('ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO %I', :'app_role')\gexec
SELECT format('ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO %I', :'app_role')\gexec
SELECT format('REVOKE UPDATE, DELETE ON TABLE audit_logs FROM %I', :'app_role')
WHERE to_regclass('public.audit_logs') IS NOT NULL\gexec
