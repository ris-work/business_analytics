.echo on
.mode box --wrap 15 --wordwrap off
.header on
.changes on
.timer on
.echo off
.print "<pre style='text-align: center; font-family: \"Cousine\", \"IBM Courier\"; color: black'>"
SELECT substring(datetime, 1, 10) as date, printf("%6.0f", itemcode) AS code, substring(olddesc,1,15) AS olddesc, printf("%9.2f", curdesc) AS curdesc FROM (SELECT sih_history_desc.datetime, sih_history_desc.itemcode, sih_history_desc.desc AS olddesc, sih_current.desc AS curdesc FROM sih_history_desc JOIN sih_current ON cast(sih_current.itemcode AS int) = sih_history_desc.itemcode) ORDER BY (datetime) DESC LIMIT 200;
.print "</pre>"
.stats
