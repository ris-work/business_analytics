BEGIN TRANSACTION;
DELETE FROM cost_purchase_import;
.import --csv cost.csv cost_purchase_import
INSERT INTO cost_purchase_current SELECT * FROM cost_purchase_import WHERE true ON CONFLICT DO UPDATE SET SIH=excluded.SIH WHERE SIH <> excluded.SIH;
--UPDATE cost SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM cost_purchase_import;
COMMIT;
VACUUM;
ANALYZE;
