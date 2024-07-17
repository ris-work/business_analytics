BEGIN TRANSACTION;
DELETE FROM vendors_import;
.import --csv cost.csv vendors_import
INSERT INTO vendors SELECT CAST(vendorcode AS INT), CAST(vendorname AS INT) FROM vendors_import WHERE true ON CONFLICT DO UPDATE SET vendorname=excluded.vendorname WHERE vendorname <> excluded.vendorname;
DELETE FROM vendors_import;
COMMIT;
VACUUM;
ANALYZE;
