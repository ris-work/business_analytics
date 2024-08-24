BEGIN TRANSACTION;
DELETE FROM tentative_revenue_import;
.import --csv cost.csv tentative_revenue_import
INSERT INTO tentative_revenue SELECT * FROM tentative_revenue_import WHERE true ON CONFLICT DO UPDATE SET quantity=excluded.quantity, sumsell = excluded.sumsell, sumcost = excluded.sumcost WHERE quantity <> excluded.quantity OR sumsell <> excluded.sumsell OR sumcost <> excluded.sumcost;
DELETE FROM tentative_revenue_import;
COMMIT;
VACUUM;
ANALYZE;
