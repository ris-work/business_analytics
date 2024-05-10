BEGIN TRANSACTION;
DELETE FROM tentative_revenue_import;
.import --csv cost.csv tentative_import
INSERT INTO tentative SELECT * FROM tentative_import WHERE true ON CONFLICT DO UPDATE SET qty=excluded.qty, sumsell = excluded.sumsell, sumcost = excluded.sumcost WHERE qty <> excluded.qty OR sumsell <> excluded.sumsell OR sumcost <> excluded.sumcost;
DELETE FROM tentative_import;
COMMIT;
VACUUM;
ANALYZE;
