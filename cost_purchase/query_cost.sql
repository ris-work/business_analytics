SELECT a.DET_PROCODE as itemcode, a.RUNNO, convert(varchar(33), a.RUNDATE, 127) as date, b.DET_CPRICE as cost FROM (SELECT DISTINCT max(CAST(HED_RUNNO AS int)) AS RUNNO, max(HED_DATE) as RUNDATE, DET_PROCODE FROM VIEW_PURCHASEDTLS GROUP BY DET_PROCODE HAVING datalength(max(HED_RUNNO)) > 0) a JOIN VIEW_PURCHASEDTLS b ON ((cast(b.HED_RUNNO AS int) = a.RUNNO) OR ((b.HED_RUNNO IS NULL) AND (a.RUNNO IS NULL))) AND a.DET_PROCODE = b.DET_PROCODE;
