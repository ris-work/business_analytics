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
	WHERE COALESCE(TRY_CAST(a.PLU_GROUP3 AS INT), -1) <> 2 AND COALESCE(TRY_CAST(a.PLU_GROUP3 AS INT), -1) <> -1
	AND c.INVDET_MODE = 'INV' ),
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
	COALESCE(TRY_CAST(s.PLU_GROUP3 AS INT), -1) AS generic,


	-- emulate STRING_AGG( HED_RUNNO+'->'+HED_REF1 , ',' )
	STUFF(
		(
			SELECT ', ' + d2.HED_RUNNO
			+ ' -> '
			+ d2.HED_REF1 + ' - ' + v2.VM_DESC
			FROM VIEW_PURCHASEDTLS d2 LEFT JOIN M_TBLVENDORS v2 ON COALESCE(TRY_CAST(d2.HED_VENCODE AS INT), -1) = COALESCE(TRY_CAST(v2.VM_CODE AS INT), -1)
			WHERE d2.DET_PROCODE = d.DET_PROCODE
			AND d2.HED_DATE   = d.HED_DATE
			FOR XML PATH(''), TYPE
			).value('.', 'NVARCHAR(MAX)')
			,1,1,'')                              AS referenceinvoices

		FROM VIEW_PURCHASEDTLS d JOIN [myPOS_DB].[dbo].[VIEW_ITEMWISESTOCKBALANCE] s ON CAST( s.PLU_CODE AS INT) = CAST(d.DET_PROCODE AS INT)
		WHERE COALESCE(TRY_CAST(s.PLU_GROUP3 AS INT), -1) <> 2 AND COALESCE(TRY_CAST(s.PLU_GROUP3 AS INT), -1) <> -1
		GROUP BY d.DET_PROCODE, d.HED_DATE, COALESCE(TRY_CAST(s.PLU_GROUP3 AS INT), -1)

	)

	SELECT 0, 0, s.totalquantity AS total_sales, p.totalquantity AS total_purchases, p.referenceinvoices AS referenceinvoices, COALESCE(s.code, p.code) AS code, COALESCE(s.invoicedate, p.invoicedate) AS invoicedate, COALESCE(s.generic, p.generic) AS generic, e.PLU_DESC FROM SALES_AGGREGATE s FULL OUTER JOIN PURCHASES p ON s.code = p.code AND s.invoicedate = p.invoicedate JOIN VIEW_ITEMWISESTOCKBALANCE e ON COALESCE(TRY_CAST(e.PLU_CODE AS int), -1) = p.code OR COALESCE(TRY_CAST(e.PLU_CODE AS int), -1) =s.code
	WHERE (s.generic IS NOT NULL AND s.generic <> -1 AND s.generic <>2)
	OR (p.generic IS NOT NULL AND p.generic <> -1 AND p.generic <>2)
	--WHERE generic = 13 AND
	-- CONVERT(varchar(10), s.invoicedate, 126)
	--      >= CONVERT(varchar(10), DATEADD(day, -180, GETDATE()), 126)
	--  AND CONVERT(varchar(10), s.invoicedate, 126)
	--      <  CONVERT(varchar(10), GETDATE(), 126)
	ORDER BY generic, s.code ASC, s.invoicedate ASC

