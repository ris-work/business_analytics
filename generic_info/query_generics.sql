SELECT COALESCE(TRY_CAST(gp_code AS INT), -1) AS category,
       1 AS classification,
       gp_desc AS description
  FROM myPOS_DB.dbo.M_TBLGROUP1
UNION ALL
SELECT COALESCE(TRY_CAST(gp_code AS INT), -1) AS category,
       2 AS classification,
       gp_desc AS description
  FROM myPOS_DB.dbo.M_TBLGROUP2
UNION ALL
SELECT COALESCE(TRY_CAST(gp_code AS INT), -1) AS category,
       3 AS classification,
       gp_desc AS description
  FROM myPOS_DB.dbo.M_TBLGROUP3
UNION ALL
SELECT COALESCE(TRY_CAST(gp_code AS INT), -1) AS category,
       4 AS classification,
       gp_desc AS description
  FROM myPOS_DB.dbo.M_TBLGROUP4
UNION ALL
SELECT COALESCE(TRY_CAST(gp_code AS INT), -1) AS category,
       5 AS classification,
       gp_desc AS description
  FROM myPOS_DB.dbo.M_TBLGROUP5
UNION ALL
SELECT COALESCE(TRY_CAST(gp_code AS INT), -1) AS category,
       6 AS classification,
       gp_desc AS description
  FROM myPOS_DB.dbo.M_TBLGROUP6;

