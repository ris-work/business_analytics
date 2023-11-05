CREATE TABLE products (ID text, TIME text, SIH int, s15 int, s30 int, s60 int, SP real) strict;
CREATE TABLE productsattime (ID text, TIME text DEFAULT CURRENT_TIMESTAMP, SIH int, s15 int, s30 int, s60 int, SP real) strict;
CREATE INDEX productsattime_daily ON productsattime (ID, substring(TIME, 0, 12));
CREATE VIEW productsattime_dailylatest AS SELECT productsattime.ID, substring(TIME, 0, 12) as date, max(TIME) as latest FROM productsattime GROUP BY id, substring(TIME, 0, 12)
/* productsattime_dailylatest(ID,date,latest) */;
