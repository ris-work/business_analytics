CREATE TABLE IF NOT EXISTS "hourly"(
"itemcode" TEXT, "daydate" TEXT, "timehour" TEXT, "quantity" TEXT);
CREATE TABLE IF NOT EXISTS "inventory"(
"productcode" TEXT, "productname" TEXT);
CREATE INDEX product_id ON hourly (itemcode);
CREATE INDEX product_id_with_date ON hourly (itemcode,daydate);
CREATE INDEX product_id_with_date_and_hour ON hourly (itemcode,daydate,timehour);
CREATE VIEW dates as WITH RECURSIVE day(x) as (VALUES(date('2019-01-01')) UNION ALL SELECT date(x, "+1 day") FROM day WHERE x<date('now')) SELECT x from day
/* dates(x) */;
CREATE VIEW cnt AS WITH RECURSIVE cnta(x) as (SELECT 0 UNION ALL SELECT x+1 FROM cnta WHERE x < 1000) SELECT x FROM cnta
/* cnt(x) */;
CREATE INDEX updated_dates ON hourly(daydate);
CREATE VIEW last_imported AS SELECT max(daydate) FROM hourly
/* last_imported("max(daydate)") */;
