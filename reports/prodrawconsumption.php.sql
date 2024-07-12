.echo on
.mode box --wrap 25 --wordwrap off
.header on
.changes on
.timer on
.echo off
.mode html
.print "</pre><div style='text-align: center; font-family: \"Cousine\", \"IBM Courier\"; color: black; white-space: pre;'><table>"
WITH trends(S_D60, S_D30, S_D15, itemcode) AS (
  SELECT
    IFNULL(total(quantity), 0) AS S_D60,
    IFNULL(total(quantity), 0) AS S_D30,
    IFNULL(total(quantity), 0) AS S_D15,
    itemcode
  FROM sih_current AS everything
  LEFT JOIN (
    SELECT total(quantity) AS S_D60, itemcode
    FROM hourly
    INDEXED BY hourly_index_for_trends_replaceme
    WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-61 days') AND date((SELECT max(daydate) FROM hourly), '-1 day')
    GROUP BY itemcode
  ) AS S60T ON everything.itemcode = S60T.itemcode
  LEFT JOIN (
    SELECT total(quantity) AS S_D30, itemcode
    FROM hourly
    INDEXED BY hourly_index_for_trends_replaceme
    WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-31 days') AND date((SELECT max(daydate) FROM hourly), '-1 day')
    GROUP BY itemcode
  ) AS S30T ON S30T.itemcode = S60T.itemcode
  LEFT JOIN (
    SELECT total(quantity) AS S_D15, itemcode
    FROM hourly
    INDEXED BY hourly_index_for_trends_replaceme
    WHERE daydate BETWEEN date((SELECT max(daydate) FROM hourly), '-16 days') AND date((SELECT max(daydate) FROM hourly), '-1 day')
    GROUP BY itemcode
  ) AS S15T ON S15T.itemcode = S30T.itemcode
)
SELECT
  printf("%.1f", total(produced.S_D60 * proportion) + soldasraw.S_D60) AS t_D60,
  printf("%.1f", total(produced.S_D30 * proportion) + soldasraw.S_D30) AS t_D30,
  printf("%.2f", total(produced.S_D15 * proportion) + soldasraw.S_D15) AS t_D15,
  printf("%.2f", sjd.sih) AS craw,
  printf("%.3f", total(djd.sih * proportion)) AS cdone,
  produced.itemcode,
  sjd.desc AS srcdesc,
  group_concat(djd.desc, CHAR(10)) AS destdesc,
  printf("%.1f", 60 * (sjd.sih + total(djd.sih * proportion)) / (total(produced.S_D60 * proportion) + soldasraw.S_D60)) AS daysl
FROM prod_list
JOIN trends AS produced ON prod_list.dest = produced.itemcode
JOIN sih_current AS sjd ON sjd.itemcode = prod_list.src
JOIN sih_current AS djd ON djd.itemcode = prod_list.dest
JOIN trends AS soldasraw ON prod_list.src = soldasraw.itemcode
GROUP BY src
ORDER BY (
  CASE WHEN CAST(t_D60 AS REAL) <> 0 THEN CAST(daysl AS REAL) ELSE 1000 END
);
.print "</table></div><br /><pre>"
.stats
