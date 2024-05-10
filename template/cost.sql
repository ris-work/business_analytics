BEGIN TRANSACTION;
DELETE FROM template_import;
.import --csv cost.csv template_import
INSERT INTO template SELECT * FROM template_import WHERE true ON CONFLICT DO UPDATE SET field=excluded.field WHERE field <> excluded.field;
DELETE FROM template_import;
COMMIT;
VACUUM;
ANALYZE;
