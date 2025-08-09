BEGIN TRANSACTION;
DELETE FROM generic_product_info_import;
.import --csv generics_product_info.csv generic_product_info_import
INSERT INTO generic_product_info SELECT cast(itemcode AS INT) AS itemcode, cast(classification AS INT) AS classification, cast(category AS INT) AS category FROM generic_product_info_import WHERE true ON CONFLICT DO UPDATE SET category = excluded.category WHERE category <> excluded.category;
--UPDATE cost SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM generic_product_info_import;
COMMIT;
--VACUUM;
--ANALYZE;
