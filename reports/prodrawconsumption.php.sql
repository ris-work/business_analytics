.echo on
.mode box --wrap 25 --wordwrap off
.header on
.changes on
.timer on
.echo off
.mode html
.print "</pre><div style='text-align: center; font-family: \"Cousine\", \"IBM Courier\"; color: black; white-space: pre;'><table>"
WITH trends AS (
  SELECT
    ifnull(S_D60, 0) AS S_D60,
    ifnull(S_D30, 0) AS S_D30,
    ifnull(S_D15, 0) AS S_D15,
    everything.itemcode AS itemcode
  FROM sih_current AS everything
  LEFT JOIN (
    SELECT total(quantity) as S_D60, itemcode
    FROM hourly INDEXED BY hourly_index_for_trends_replaceme
    WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-61 days')
                         AND date((SELECT max(daydate) FROM hourly), '-1 day')
    GROUP BY itemcode
  ) S60T ON everything.itemcode = S60T.itemcode
  LEFT JOIN (
    SELECT total(quantity) AS S_D30, itemcode
    FROM hourly INDEXED BY hourly_index_for_trends_replaceme
    WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-31 days')
                         AND date((SELECT max(daydate) FROM hourly), '-1 day')
    GROUP BY itemcode
  ) S30T ON S30T.itemcode = S60T.itemcode
  LEFT JOIN (
    SELECT total(quantity) AS S_D15, itemcode
    FROM hourly INDEXED BY hourly_index_for_trends_replaceme
    WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-16 days')
                         AND date((SELECT max(daydate) FROM hourly), '-1 day')
    GROUP BY itemcode
  ) AS S15T ON S15T.itemcode = S30T.itemcode
)
SELECT
  printf("%6.1f", total(produced.S_D60*proportion) + soldasraw.S_D60) AS t_D60,
  printf("%5.1f", total(produced.S_D30*proportion) + soldasraw.S_D30) AS t_D30,
  printf("%6.2f", total(produced.S_D15*proportion) + soldasraw.S_D15) AS t_D15,
  printf("%6.2f", sjd.sih) AS craw,
  printf("%7.3f", total(djd.sih*proportion)) AS cdone,
  produced.itemcode,
  sjd.desc AS srcdesc,
  group_concat(djd.desc, CHAR(10)) AS destdesc,
  printf("%5.1f", 60 * (sjd.sih+total(djd.sih*proportion))/(total(produced.S_D60*proportion) + soldasraw.S_D60)) AS daysl
FROM prod_list
JOIN trends produced ON prod_list.dest = produced.itemcode
JOIN sih_current sjd ON sjd.itemcode = prod_list.src
JOIN sih_current djd ON djd.itemcode = prod_list.dest
JOIN trends soldasraw ON prod_list.src = soldasraw.itemcode
GROUP BY src
ORDER BY (CASE WHEN CAST(t_D60 AS REAL)<>0 THEN CAST(daysl AS REAL) ELSE 1000 END);
.print "</table></div><br /><pre>"
.stats
