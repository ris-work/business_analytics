BEGIN TRANSACTION;
DELETE FROM selling_import;
.import --csv cost.csv selling_import
INSERT INTO selling SELECT CAST(code AS INT), CAST(sell AS REAL) FROM selling_import WHERE true ON CONFLICT DO UPDATE SET sell=excluded.sell WHERE sell <> excluded.sell;
--UPDATE cost SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM selling_import;
COMMIT;
VACUUM;
ANALYZE;
