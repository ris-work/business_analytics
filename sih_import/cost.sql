BEGIN TRANSACTION;
DELETE FROM sih_import;
.import --csv cost.csv sih_import
INSERT INTO sih_current SELECT * FROM sih_import WHERE true ON CONFLICT DO UPDATE SET SIH=excluded.SIH WHERE SIH <> excluded.SIH;
--UPDATE cost SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM sih_import;
COMMIT;
VACUUM;
ANALYZE;
