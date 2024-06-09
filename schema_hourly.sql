CREATE TABLE hourly_changes('DATETIME', 'itemcode', 'daydate', 'timehour', 'qty', 'qty_change');
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
CREATE TABLE selling(itemcode TEXT, sell TEXT, PRIMARY KEY (itemcode));
CREATE TABLE IF NOT EXISTS "selling_import"(
"code" TEXT, "Column1" TEXT);
CREATE TABLE prod_list(dest TEXT NOT NULL, src TEXT NOT NULL, cost_src TEXT NOT NULL, proportion TEXT NOT NULL, PRIMARY KEY (dest, src)) STRICT;
CREATE TABLE IF NOT EXISTS "prod_list_import"(
"dest" TEXT, "src" TEXT, "cost_src" TEXT, "proportion" TEXT);
CREATE TABLE inventory(itemcode TEXT NOT NULL, productname TEXT, PRIMARY KEY (itemcode)) STRICT;
CREATE TABLE IF NOT EXISTS "inventory_import"(
"itemcode" TEXT, "productname" TEXT);
CREATE TABLE IF NOT EXISTS "hourly_import"(
"000001" TEXT, "2021-08-07" TEXT, "10" TEXT, "20.000" TEXT,
 "960.00" TEXT, "834.80000" TEXT);
CREATE TABLE IF NOT EXISTS "tentative_revenue_import"(
"productcode" TEXT, "daydate" TEXT, "timehour" TEXT, "qty" TEXT,
 "sumsell" TEXT, "sumcost" TEXT);
CREATE TABLE tentative_revenue ("itemcode" TEXT, "daydate" TEXT, "timehour" TEXT, "quantity" TEXT, sumsell NOT NULL DEFAULT 0, sumcost NOT NULL DEFAULT 0, PRIMARY KEY (itemcode, daydate, timehour)) WITHOUT ROWID;
CREATE TABLE hourly ("itemcode" TEXT, "daydate" TEXT, "timehour" TEXT, "quantity" TEXT, sumsell NOT NULL DEFAULT 0, sumcost NOT NULL DEFAULT 0, PRIMARY KEY (itemcode, daydate, timehour)) WITHOUT ROWID;
CREATE TABLE IF NOT EXISTS "full_inventory_current_import"(
"itemcode" TEXT, "sell" TEXT, "cost" TEXT);
CREATE TABLE full_inventory_current (itemcode INT, sell REAL, cost REAL, PRIMARY KEY (itemcode)) STRICT;
CREATE TABLE sih_history(datetime TEXT NOT NULL, itemcode INT NOT NULL, desc TEXT, sih INT, cost REAL, sell REAL, PRIMARY KEY (datetime, itemcode)) STRICT;
CREATE TABLE full_inventory_history (datetime TEXT NOT NULL, itemcode INT NOT NULL, sell REAL, cost REAL, primary key(datetime, itemcode)) STRICT;
CREATE INDEX cost_purchase_itemcode ON cost_purchase(itemcode);
CREATE INDEX sih_current_description_sih ON sih_current(itemcode, desc, sih);
CREATE INDEX tentative_revenue_everything ON tentative_revenue(itemcode, daydate, timehour, sumsell, sumcost);
CREATE INDEX product_id_with_date_and_hour ON hourly (itemcode,daydate,timehour);
CREATE INDEX updated_dates ON hourly(daydate);
CREATE INDEX full_inventory_current_covering ON full_inventory_current(itemcode, sell, cost);
CREATE INDEX sih_cast_covering ON sih_current(itemcode, desc, sih, cost, sell, cast(itemcode as int));
CREATE INDEX hourly_index_for_trends ON hourly(itemcode, daydate, quantity);
CREATE INDEX selling_covering ON selling(itemcode, sell);
CREATE INDEX cost_purchase_covering ON cost_purchase(itemcode, runno, date, cost);
CREATE INDEX hourly_index_for_trends_replaceme ON hourly(daydate, itemcode, quantity);
CREATE VIEW dates as WITH RECURSIVE day(x) as (VALUES(date('2019-01-01')) UNION ALL SELECT date(x, '+1 day') FROM day WHERE x<date('now')) SELECT x from day
/* dates(x) */;
CREATE VIEW cnt AS WITH RECURSIVE cnta(x) as (SELECT 0 UNION ALL SELECT x+1 FROM cnta WHERE x < 1000) SELECT x FROM cnta
/* cnt(x) */;
CREATE VIEW last_imported AS SELECT max(daydate) FROM hourly
/* last_imported("max(daydate)") */;
CREATE VIEW hourly_existing_entries AS SELECT itemcode, daydate, timehour FROM hourly
/* hourly_existing_entries(itemcode,daydate,timehour) */;
CREATE VIEW hourly_import_existing_entries AS SELECT itemcode, daydate, timehour FROM hourly_import;
CREATE VIEW zeroed_out_on_import AS SELECT itemcode, daydate, timehour FROM hourly EXCEPT SELECT itemcode, daydate, timehour FROM hourly_import;
CREATE VIEW everything_itemcode_in_hourly AS SELECT DISTINCT itemcode FROM hourly
/* everything_itemcode_in_hourly(itemcode) */;
CREATE VIEW t_sumrev AS SELECT itemcode, sum(sumsell) AS cumulativesell, sum(sumcost) AS cumulativecost FROM hourly GROUP BY itemcode
/* t_sumrev(itemcode,cumulativesell,cumulativecost) */;
CREATE TRIGGER hourly_changes_logger BEFORE UPDATE ON hourly FOR EACH ROW BEGIN INSERT INTO hourly_changes VALUES (date()||'T'||time(), OLD.itemcode, OLD.daydate, OLD.timehour, OLD.quantity, OLD.quantity - NEW.quantity); END;
CREATE TRIGGER full_inventory_history_logger AFTER UPDATE ON full_inventory_current FOR EACH ROW WHEN OLD.sell <> NEW.sell OR OLD.cost <> NEW.cost BEGIN INSERT INTO full_inventory_history VALUES (datetime(), OLD.itemcode, OLD.sell, OLD.cost); END;
CREATE TRIGGER sih_history_logger AFTER UPDATE ON sih_current FOR EACH ROW WHEN OLD.sih <> NEW.sih OR OLD.sell <> NEW.sell OR OLD.cost <> NEW.cost BEGIN INSERT INTO sih_history VALUES (datetime(), cast(OLD.itemcode as int), "", cast(OLD.sih AS INT), cast(OLD.cost AS REAL), cast(OLD.sell AS REAL)); END;
CREATE VIEW full_inventory_history_latest AS SELECT latest.itemcode, cost, sell FROM (SELECT itemcode, max(datetime) AS dt FROM full_inventory_history WHERE cost <> 0 AND sell <> 0 GROUP BY itemcode) latest JOIN full_inventory_history history ON latest.itemcode = history.itemcode AND latest.dt = history.datetime
/* full_inventory_history_latest(itemcode,cost,sell) */;
