SELECT
  CAST(t.PLU_CODE AS INT)               AS ItemCode,
  CAST(v.ClassificationNumber AS INT)                AS ClassificationNumber,
  CAST(v.GroupNumber AS INT)                         AS GroupNumber
FROM VIEW_ITEMWISESTOCKBALANCE AS t
CROSS APPLY (VALUES
    (1, t.PLU_GROUP1),
    (2, t.PLU_GROUP2),
    (3, t.PLU_GROUP3),
    (4, t.PLU_GROUP4),
    (5, t.PLU_GROUP5),
    (6, t.PLU_GROUP6)
) AS v(ClassificationNumber, GroupNumber)
WHERE 
  v.GroupNumber IS NOT NULL
  AND v.GroupNumber <> 'N/A'
ORDER BY 
  ItemCode,
  ClassificationNumber
