BEGIN TRANSACTION;
DELETE FROM cost_purchase_import;
.import --csv cost.csv cost_purchase_import
INSERT INTO cost_purchase SELECT cast(itemcode AS INT), cast(runno AS INT), date, cast(cost AS REAL) FROM cost_purchase_import WHERE true ON CONFLICT DO UPDATE SET RUNNO=excluded.RUNNO, date = excluded.date, cost = excluded.cost WHERE true; --WHERE SIH <> excluded.SIH;
--UPDATE cost SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM cost_purchase_import;
COMMIT;
VACUUM;
ANALYZE;
