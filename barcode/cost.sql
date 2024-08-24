BEGIN TRANSACTION;
DELETE FROM barcodes_import;
.import --csv cost.csv barcodes_import
INSERT INTO barcodes SELECT CAST(barcode AS int), CAST(itemcode AS int) FROM barcodes_import WHERE true ON CONFLICT DO NOTHING;
DELETE FROM barcodes_import;
COMMIT;
VACUUM;
ANALYZE;
