WITH SALE AS (SELECT [PLU_CODE] AS code
      ,[PLU_GROUP3] AS generic_a
      ,[SIH] AS sih
      ,[PLU_UNIT] AS units
	  ,b.GP_CODE AS generic_b
	  ,b.GP_DESC AS genericname
	  ,c.INVDET_UNITQTY AS quantity
	  ,CONVERT(varchar(10), c.INVDET_TXNDATE, 126) AS invoicedate

  FROM [myPOS_DB].[dbo].[VIEW_ITEMWISESTOCKBALANCE] a

  JOIN M_TBLGROUP3 b ON COALESCE(TRY_CAST(a.PLU_GROUP3 AS INT), -1) = COALESCE(TRY_CAST(b.GP_CODE AS INT), -1) JOIN T_TBLINVDETAILS c ON COALESCE(TRY_CAST(c.INVDET_PROCODE AS int), -1) = COALESCE(TRY_CAST(a.PLU_CODE AS int), -1)
    WHERE c.INVDET_MODE = 'INV' ),
	SALES_AGGREGATE AS (
	SELECT code, sum(quantity) AS totalquantity, invoicedate, COALESCE(TRY_CAST(generic_a AS int), -1) AS generic FROM SALE GROUP BY invoicedate, code, COALESCE(TRY_CAST(generic_a AS int), -1)
	),
	PURCHASES AS (
	  SELECT
    COALESCE(TRY_CAST(d.DET_PROCODE AS INT), -1)           AS code,
    CONVERT(varchar(10), d.HED_DATE,126) AS invoicedate,
    SUM(d.DET_CASESIZE*d.DET_CASEQTY 
        + d.DET_UNITQTY 
        + d.DET_FREEQTY)                AS totalquantity,

    -- emulate STRING_AGG( HED_RUNNO+'->'+HED_REF1 , ',' )
    STUFF(
      (
        SELECT ', ' + d2.HED_RUNNO
               + ' -> ' 
               + d2.HED_REF1
        FROM VIEW_PURCHASEDTLS d2
        WHERE d2.DET_PROCODE = d.DET_PROCODE
          AND d2.HED_DATE   = d.HED_DATE
        FOR XML PATH(''), TYPE
      ).value('.', 'NVARCHAR(MAX)')
    ,1,1,'')                              AS referenceinvoices

  FROM VIEW_PURCHASEDTLS d
  GROUP BY d.DET_PROCODE, d.HED_DATE
	
	)

	SELECT TOP(1000) SUM(s.totalquantity) OVER (
  PARTITION BY s.code
  ORDER BY s.invoicedate
  ROWS UNBOUNDED PRECEDING
) AS cumulative_total_sales,SUM(p.totalquantity) OVER (
  PARTITION BY p.code
  ORDER BY p.invoicedate
  ROWS UNBOUNDED PRECEDING
) AS cumulative_total_purchases, s.totalquantity AS total_sales, p.totalquantity AS total_purchases, p.referenceinvoices AS referenceinvoices, COALESCE(s.code, p.code) AS code, COALESCE(s.invoicedate, p.invoicedate) AS invoicedate, e.PLU_DESC FROM SALES_AGGREGATE s FULL OUTER JOIN PURCHASES p ON s.code = p.code AND s.invoicedate = p.invoicedate JOIN VIEW_ITEMWISESTOCKBALANCE e ON COALESCE(TRY_CAST(e.PLU_CODE AS int), -1) = p.code OR COALESCE(TRY_CAST(e.PLU_CODE AS int), -1) =s.code 

--WHERE generic = 13 AND 
-- CONVERT(varchar(10), s.invoicedate, 126) 
--      >= CONVERT(varchar(10), DATEADD(day, -180, GETDATE()), 126)
--  AND CONVERT(varchar(10), s.invoicedate, 126) 
--      <  CONVERT(varchar(10), GETDATE(), 126)
ORDER BY generic, s.code ASC, s.invoicedate ASC 
