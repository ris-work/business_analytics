BEGIN TRANSACTION;
DELETE FROM full_inventory_current_import;
.import --csv cost.csv full_inventory_current_import
INSERT INTO full_inventory_current SELECT CAST(itemcode AS INT) as itemcode, CAST(sell AS REAL) as sell, CAST(cost AS REAL) as cost FROM full_inventory_current_import WHERE true ON CONFLICT DO UPDATE SET sell = CAST(excluded.sell AS REAL), cost = CAST(excluded.cost AS REAL) WHERE sell <> CAST(excluded.sell AS REAL) OR cost <> CAST(excluded.cost AS REAL);
DELETE FROM full_inventory_current_import;
COMMIT;
VACUUM;
ANALYZE;
