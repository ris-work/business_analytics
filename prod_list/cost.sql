BEGIN TRANSACTION;
DELETE FROM prod_list_import;
.import --csv cost.csv prod_list_import
INSERT INTO prod_list SELECT * FROM prod_list_import WHERE true ON CONFLICT DO UPDATE SET cost_src=excluded.cost_src, proportion=excluded.proportion WHERE cost_src <> excluded.cost_src OR proportion <> excluded.proportion;
--UPDATE cost SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM prod_list_import;
COMMIT;
VACUUM;
ANALYZE;
