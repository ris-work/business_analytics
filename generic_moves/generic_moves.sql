BEGIN TRANSACTION;
DELETE FROM generic_moves_import;
.import --csv generic_moves.csv generic_moves_import
--INSERT INTO cost SELECT cast(cumulative_total_sales AS REAL) AS cumulative_total_sales, cast(cumulative_total_purchases AS REAL) AS cumulative_total_purchases, cast(total_sales AS REAL) AS total_sales, cast(total_purchases AS REAL) AS total_purchases, referenceinvoices, cast(code AS INT) AS code, invoicedate, description FROM generic_moves_import WHERE true ON CONFLICT DO UPDATE SET cost=CAST(excluded.cost AS REAL) WHERE cost <> CAST(excluded.cost AS REAL);
--oUPDATE cost SET quantity=0 WHERE (itemcode, daydate, timehour) IN zeroed_out_on_import;
INSERT INTO cost (
  cumulative_total_sales,
  cumulative_total_purchases,
  total_sales,
  total_purchases,
  referenceinvoices,
  code,
  invoicedate,
  description
)
SELECT
  CAST(cumulative_total_sales   AS REAL) AS cumulative_total_sales,
  CAST(cumulative_total_purchases AS REAL) AS cumulative_total_purchases,
  CAST(total_sales               AS REAL) AS total_sales,
  CAST(total_purchases           AS REAL) AS total_purchases,
  referenceinvoices,
  CAST(code                      AS INT ) AS code,
  invoicedate,
  description
FROM generic_moves_import
WHERE true
ON CONFLICT DO UPDATE
  SET
    cumulative_total_sales    = excluded.cumulative_total_sales,
    cumulative_total_purchases= excluded.cumulative_total_purchases,
    total_sales               = excluded.total_sales,
    total_purchases           = excluded.total_purchases,
    referenceinvoices         = excluded.referenceinvoices,
    description               = excluded.description;

DELETE FROM cost_import;
COMMIT;
--VACUUM;
--ANALYZE;
