CREATE TABLE IF NOT EXISTS "inventory"(
"productcode" TEXT, "productname" TEXT);
CREATE TABLE hourly ("itemcode" TEXT, "daydate" TEXT, "timehour" TEXT, "quantity" TEXT, PRIMARY KEY (itemcode, daydate, timehour));
CREATE TABLE hourly_changes('DATETIME', 'itemcode', 'daydate', 'timehour', 'qty', 'qty_change');
CREATE TABLE hourly_import ('itemcode', 'daydate', 'timehour', "quantity");
CREATE TABLE sqlite_stat1(tbl,idx,stat);
CREATE TABLE cost(itemcode TEXT, daydate TEXT, cost TEXT, PRIMARY KEY (itemcode, daydate));
CREATE TABLE cost_import(itemcode TEXT, daydate TEXT, cost TEXT);
CREATE TABLE cost_purchase(itemcode TEXT, runno TEXT, date TEXT, cost TEXT, PRIMARY KEY (itemcode));
CREATE TABLE IF NOT EXISTS "cost_purchase_import"(
"itemcode" TEXT, "RUNNO" TEXT, "date" TEXT, "cost" TEXT);
CREATE TABLE sih_current(itemcode TEXT, desc TEXT, sih TEXT, cost TEXT, sell TEXT, PRIMARY KEY (itemcode));
CREATE TABLE IF NOT EXISTS "sih_import"(
"PLU_CODE" TEXT, "PLU_DESC" TEXT, "SIH" TEXT, "COSTVALUE" TEXT,
 "SELLVALUE" TEXT);
CREATE INDEX product_id ON hourly (itemcode);
CREATE INDEX product_id_with_date ON hourly (itemcode,daydate);
CREATE INDEX product_id_with_date_and_hour ON hourly (itemcode,daydate,timehour);
CREATE INDEX updated_dates ON hourly(daydate);
CREATE INDEX hourly_import_comparison ON hourly_import(itemcode, daydate, timehour);
CREATE INDEX cost_purchase_itemcode ON cost_purchase(itemcode);
CREATE VIEW dates as WITH RECURSIVE day(x) as (VALUES(date('2019-01-01')) UNION ALL SELECT date(x, '+1 day') FROM day WHERE x<date('now')) SELECT x from day
/* dates(x) */;
CREATE VIEW cnt AS WITH RECURSIVE cnta(x) as (SELECT 0 UNION ALL SELECT x+1 FROM cnta WHERE x < 1000) SELECT x FROM cnta
/* cnt(x) */;
CREATE VIEW last_imported AS SELECT max(daydate) FROM hourly
/* last_imported("max(daydate)") */;
CREATE TRIGGER hourly_changes_logger BEFORE UPDATE ON hourly FOR EACH ROW BEGIN INSERT INTO hourly_changes VALUES (date()||'T'||time(), OLD.itemcode, OLD.daydate, OLD.timehour, OLD.quantity, OLD.quantity - NEW.quantity); END;
CREATE VIEW hourly_existing_entries AS SELECT itemcode, daydate, timehour FROM hourly
/* hourly_existing_entries(itemcode,daydate,timehour) */;
CREATE VIEW hourly_import_existing_entries AS SELECT itemcode, daydate, timehour FROM hourly_import
/* hourly_import_existing_entries(itemcode,daydate,timehour) */;
CREATE VIEW zeroed_out_on_import AS SELECT itemcode, daydate, timehour FROM hourly EXCEPT SELECT itemcode, daydate, timehour FROM hourly_import
/* zeroed_out_on_import(itemcode,daydate,timehour) */;
