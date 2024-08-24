.echo on
.mode box --wrap 15 --wordwrap off
.header on
.changes on
.timer on
.echo off
.print "<pre style='text-align: center; font-family: \"Cousine\", \"IBM Courier\"; color: black'>"
SELECT printf("%6.0f", itemcode) AS code, substring(desc,1,15) AS desc, printf("%9.2f", newcost) AS newcost, printf("%9.2f", oldcost) AS oldcost, printf("%9.2f", newcost-oldcost) AS costchg, printf("%9.2f", newsell) AS newsell, printf("%9.2f", oldsell) AS oldsell, printf("%9.2f", newsell-oldsell) AS sellchg FROM (SELECT full_inventory_history_latest.itemcode, full_inventory_history_latest.cost AS oldcost, full_inventory_current.cost AS newcost, full_inventory_history_latest.sell AS oldsell, full_inventory_current.sell AS newsell, sih_current.desc FROM full_inventory_history_latest JOIN full_inventory_current ON full_inventory_history_latest.itemcode = full_inventory_current.itemcode JOIN sih_current ON cast(sih_current.itemcode AS int) = full_inventory_history_latest.itemcode) ORDER BY (newcost-oldcost) DESC;
.print "</pre>"
.stats
