.echo on
.mode box
.header on
.changes on
.timer on
SELECT tr_p.*, sih_current.desc, sih FROM (select sum(sumsell-sumcost) AS gp, sum(sumsell) AS sp, sum(sumcost) AS cp , itemcode from tentative_revenue GROUP BY itemcode) tr_p LEFT JOIN sih_current ON sih_current.itemcode=tr_p.itemcode WHERE sih_current.sih<1 OR sih_current.sih IS NULL ORDER BY sih ASC, gp DESC LIMIT 5;
