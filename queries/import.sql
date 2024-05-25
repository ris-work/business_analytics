BEGIN TRANSACTION;
DELETE FROM hourly_import;
.import --csv sample.csv hourly_import
INSERT INTO hourly SELECT * FROM hourly_import WHERE true ON CONFLICT DO UPDATE SET quantity=excluded.quantity WHERE quantity <> excluded.quantity;
--UPDATE hourly SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM hourly_import;
COMMIT;
VACUUM;
ANALYZE;
