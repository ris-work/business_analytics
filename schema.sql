CREATE TABLE products (ID text, TIME text, SIH int, s15 int, s30 int, s60 int, SP real) strict;
CREATE TABLE productsattime (ID text, TIME text DEFAULT CURRENT_TIMESTAMP, SIH int, s15 int, s30 int, s60 int, SP real) strict;
CREATE TABLE productsattime_misc (ID text, TIME text DEFAULT CURRENT_TIMESTAMP, DESC text, BARCODE text, SELL real) strict;
CREATE INDEX productsattime_daily ON productsattime (ID, substring(TIME, 0, 12));
CREATE INDEX productsattime_idx_172303f8 ON productsattime(ID, TIME);
CREATE VIEW productsattime_dailylatest AS SELECT productsattime.ID, substring(TIME, 0, 12) as date, max(TIME) as latest FROM productsattime GROUP BY id, substring(TIME, 0, 12)
/* productsattime_dailylatest(ID,date,latest) */;
CREATE VIEW productsattime_misclatest AS SELECT A.ID, productsattime_misc.TIME, DESC, SELL from productsattime_misc JOIN (select ID, max(TIME) as mt from productsattime_misc GROUP BY ID) as A ON A.ID=productsattime_misc.ID AND mt=productsattime_misc.TIME
/* productsattime_misclatest(ID,TIME,"DESC",SELL) */;
