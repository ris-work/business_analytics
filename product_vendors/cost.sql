BEGIN TRANSACTION;
DELETE FROM product_vendors_import;
.import --csv cost.csv product_vendors_import
INSERT INTO product_vendors SELECT CAST(itemcode AS INT), CAST(vendorcode AS INT), CAST(cost AS REAL), CAST(sell AS REAL) FROM product_vendors_import WHERE true ON CONFLICT DO UPDATE SET cost=CAST(excluded.cost AS REAL), sell = CAST(excluded.sell AS REAL) WHERE cost <> CAST(excluded.cost AS REAL) OR sell <> CAST(excluded.sell AS REAL);
DELETE FROM product_vendors_import;
COMMIT;
VACUUM;
ANALYZE;
