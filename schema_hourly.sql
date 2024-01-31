CREATE TABLE IF NOT EXISTS "hourly"(
"itemcode" TEXT, "daydate" TEXT, "timehour" TEXT, "quantity" TEXT);
CREATE TABLE IF NOT EXISTS "inventory"(
"productcode" TEXT, "productname" TEXT);
CREATE TABLE dates(x);
CREATE INDEX product_id ON hourly (itemcode);
CREATE INDEX product_id_with_date ON hourly (itemcode,daydate);
CREATE INDEX product_id_with_date_and_hour ON hourly (itemcode,daydate,timehour);
