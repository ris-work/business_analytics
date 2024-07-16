BEGIN TRANSACTION;
DELETE FROM cost_import;
.import --csv cost.csv cost_import
INSERT INTO cost SELECT cast(itemcode AS INT), daydate, cast(cost AS REAL) FROM cost_import WHERE true ON CONFLICT DO UPDATE SET cost=excluded.cost WHERE cost <> excluded.cost;
--UPDATE cost SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM cost_import;
COMMIT;
VACUUM;
ANALYZE;
