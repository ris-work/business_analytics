.echo on
.mode box
.header on
.changes on
.timer on
SELECT *, newcost-oldcost AS costchg, newsell-oldsell AS sellchg FROM (SELECT full_inventory_history_latest.cost AS oldcost, full_inventory_current.cost AS newcost, full_inventory_history_latest.sell AS oldsell, full_inventory_current.sell AS newsell, sih_current.desc FROM full_inventory_history_latest JOIN full_inventory_current ON full_inventory_history_latest.itemcode = full_inventory_current.itemcode JOIN sih_current ON cast(sih_current.itemcode AS int) = full_inventory_history_latest.itemcode) ORDER BY costchg DESC;
