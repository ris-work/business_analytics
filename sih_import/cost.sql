BEGIN TRANSACTION;
DELETE FROM sih_import;
.import --csv cost.csv sih_import
INSERT INTO sih_current SELECT cast(PLU_CODE AS INT), PLU_DESC, cast(SIH AS REAL), cast(COSTVALUE AS REAL), cast(SELLVALUE AS REAL) FROM sih_import WHERE true ON CONFLICT DO UPDATE SET SIH=CAST(excluded.SIH AS REAL), desc=excluded.desc WHERE SIH <> CAST(excluded.SIH AS REAL) OR desc <> excluded.desc;
--UPDATE cost SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
DELETE FROM sih_import;
COMMIT;
VACUUM;
ANALYZE;
