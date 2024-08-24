BEGIN TRANSACTION;
DELETE FROM hourly_import;
.import --csv sample.csv hourly_import
INSERT INTO hourly SELECT CAST(itemcode AS int), daydate, CAST(timehour AS int), CAST(quantity AS int), CAST(sumsell AS REAL), CAST(sumcost AS REAL) FROM hourly_import WHERE true ON CONFLICT DO UPDATE SET quantity=CAST(excluded.quantity AS INT), sumsell=CAST(excluded.sumsell AS REAL), sumcost=CAST(excluded.sumcost AS REAL) WHERE quantity <> CAST(excluded.quantity AS INT) OR sumsell <> CAST(excluded.sumsell AS REAL) OR sumcost <> CAST(excluded.sumcost AS REAL);
--UPDATE hourly SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM hourly_import;
COMMIT;
VACUUM;
ANALYZE;
