.echo on
.mode box --wrap 25 --wordwrap off
.header on
.changes on
.timer on
.echo off
.mode html
.print "</pre><div style='text-align: center; font-family: \"Cousine\", \"IBM Courier\"; color: black; white-space: pre;'><table>"
SELECT substring(datetime, 1, 10) as date, printf("%6.0f", itemcode) AS code, substring(olddesc,1,35) AS olddesc, substring(curdesc, 1, 35) AS curdesc FROM (SELECT sih_history_desc.datetime, sih_history_desc.itemcode, sih_history_desc.desc AS olddesc, sih_current.desc AS curdesc FROM sih_history_desc JOIN sih_current ON cast(sih_current.itemcode AS int) = sih_history_desc.itemcode) ORDER BY (datetime) DESC LIMIT 200;
.print "</table></div><br /><pre>"
.stats
