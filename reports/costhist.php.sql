.echo on
.mode box --wrap 15 --wordwrap off
.header on
.changes on
.timer on
.echo off
.print "<pre style='text-align: center; font-family: \"Cousine\", \"IBM Courier\"; color: black'>"
SELECT substring(datetime, 1, 10) as date, printf("%6.0f", itemcode) AS code, substring(desc,1,15) AS desc, printf("%9.2f", curcost) AS curcost, printf("%9.2f", oldcost) AS oldcost, printf("%9.2f", curcost-oldcost) AS costchg, printf("%9.2f", cursell) AS cursell, printf("%9.2f", oldsell) AS oldsell, printf("%9.2f", cursell-oldsell) AS sellchg FROM (SELECT full_inventory_history.datetime, full_inventory_history.itemcode, full_inventory_history.cost AS oldcost, full_inventory_current.cost AS curcost, full_inventory_history.sell AS oldsell, full_inventory_current.sell AS cursell, sih_current.desc FROM full_inventory_history JOIN full_inventory_current ON full_inventory_history.itemcode = full_inventory_current.itemcode JOIN sih_current ON cast(sih_current.itemcode AS int) = full_inventory_history.itemcode WHERE oldcost <> 0) ORDER BY (datetime) DESC LIMIT 200;
.print "</pre>"
.stats
