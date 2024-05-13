.echo on
.mode box
.header on
.changes on
.timer on
.echo off
.print "<pre style='text-align: center; font-family: \"Cousine\", \"IBM Courier\"; color: black'>"
SELECT printf('%,12.2f', gp) as gp, printf("%,12.2f", sp) as sp, printf("%,12.2f", cp) as cp, desc, printf("%,5.f", sih) AS sih FROM (SELECT tr_p.sp, tr_p.gp, tr_p.cp, sih_current.desc, CAST (sih_current.sih AS REAL) AS sih FROM (SELECT sum(sumsell-sumcost) AS gp, sum(sumsell) AS sp, sum(sumcost) AS cp , itemcode from tentative_revenue INDEXED BY tentative_revenue_everything GROUP BY itemcode) tr_p LEFT JOIN sih_current ON sih_current.itemcode=tr_p.itemcode WHERE sih IS NOT NULL ORDER BY gp DESC LIMIT 200);
.print "</pre>"
.stats
