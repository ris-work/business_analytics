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
.print "LAST SOLD () DAYS AGO"
.print "</pre><div style='text-align: center; font-family: \"Cousine\", \"IBM Courier\"; color: black; white-space: pre;' class='table-container'>LAST SOLD () DAYS AGO<br /><table>"
WITH days_since_last_sold AS 
	(SELECT julianday('now') - julianday(max(daydate)) AS days_since_last_sold, 
		itemcode
		FROM hourly 
		GROUP BY itemcode 
		HAVING days_since_last_sold), 
	days_since_last_bought AS 
	(SELECT julianday('now') - julianday(date) AS days_since_last_bought,
		itemcode
		FROM cost_purchase)
SELECT sih_current.itemcode AS code, 
	printf('%3.f', sih) AS sih, 
	printf('%4.0f', min(days_since_last_sold, days_since_last_bought)) AS d, 
	desc, sell, printf('%4.1f', days_since_last_bought) AS db, 
    printf('%4.1f', days_since_last_sold) AS ds 
FROM sih_current JOIN days_since_last_sold 
ON days_since_last_sold.itemcode = sih_current.itemcode 
JOIN days_since_last_bought
ON days_since_last_bought.itemcode = sih_current.itemcode
WHERE sih_current.sih > 0 AND 
sih_current.itemcode NOT IN 
	(SELECT src FROM prod_list) 
ORDER BY min(days_since_last_sold, days_since_last_bought) DESC 
LIMIT 100;
.print "</table></div><br /><pre>"
.stats
