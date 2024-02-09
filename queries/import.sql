BEGIN TRANSACTION;
DELETE FROM hourly_import;
.import --csv sample.csv hourly_import
INSERT INTO hourly SELECT * FROM hourly_import WHERE true ON CONFLICT DO UPDATE SET quantity=excluded.quantity WHERE quantity <> excluded.quantity;
COMMIT;
