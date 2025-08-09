BEGIN TRANSACTION;
DELETE FROM generic_info;
.import --csv generic_info.csv generic_info_import
INSERT INTO generic_info SELECT cast(category AS INT) AS category, cast(classification AS INT) AS classification, description FROM generic_info_import WHERE true ON CONFLICT DO UPDATE SET description=excluded.description WHERE description <> excluded.description;
--UPDATE cost SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM generic_info_import;
COMMIT;
--VACUUM;
--ANALYZE;
