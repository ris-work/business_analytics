.echo off
.mode box --wrap 25 --wordwrap off
.header on
.changes on
.timer on
.echo off
.mode html
--pragma temp_store_directory='/www';
--pragma temp_directory='/www';
--pragma temp_store=MEMORY;
.print "</pre><div style='text-align: center; font-family: \"Cousine\", \"IBM Courier\"; color: black; white-space: pre;'><table>"
WITH days_since_last_sold AS 
	(SELECT julianday('now') - julianday(max(daydate)) AS days_since_last_sold, 
		itemcode 
		FROM hourly 
		GROUP BY itemcode 
		HAVING days_since_last_sold) 
SELECT sih_current.itemcode, desc, sih, sell, 
	printf('%6.2f', days_since_last_sold) AS this_long_ago 
FROM sih_current JOIN days_since_last_sold 
ON days_since_last_sold.itemcode = sih_current.itemcode 
WHERE sih_current.sih > 0 AND 
sih_current.itemcode NOT IN 
	(SELECT src FROM prod_list) 
ORDER BY days_since_last_sold DESC 
LIMIT 100;
.print "</table></div><br /><pre>"
.stats
