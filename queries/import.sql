BEGIN TRANSACTION;
DELETE FROM hourly_import;
.import --csv sample.csv hourly_import
INSERT INTO hourly SELECT CAST(itemcode AS int), daydate, CAST(timehour AS int), CAST(quantity AS int), CAST(sumsell AS REAL), CAST(sumcost AS REAL) FROM hourly_import WHERE true ON CONFLICT DO UPDATE SET quantity=excluded.quantity, sumsell=excluded.sumsell, sumcost=excluded.sumcost WHERE quantity <> excluded.quantity OR sumsell <> excluded.sumsell OR sumcost <> excluded.sumcost;
--UPDATE hourly SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM hourly_import;
COMMIT;
VACUUM;
ANALYZE;
