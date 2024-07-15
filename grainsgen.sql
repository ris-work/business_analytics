PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE grains(name TEXT, cost REAL, sell REAL, PRIMARY KEY (name)) WITHOUT ROWID;
INSERT INTO grains VALUES('AMARANTH',1000.0,1200.0);
INSERT INTO grains VALUES('BARLEY',1000.0,1200.0);
INSERT INTO grains VALUES('BLACK GRAM',1000.0,1200.0);
INSERT INTO grains VALUES('BUCKWHEAT',1000.0,1200.0);
INSERT INTO grains VALUES('CHIA',1000.0,1200.0);
INSERT INTO grains VALUES('CHICKPEA',1000.0,1200.0);
INSERT INTO grains VALUES('COWPEA',1000.0,1200.0);
INSERT INTO grains VALUES('FLAX',1000.0,1200.0);
INSERT INTO grains VALUES('GREEN GRAM',1000.0,1200.0);
INSERT INTO grains VALUES('MILLET',1000.0,1200.0);
INSERT INTO grains VALUES('MUSTARD',1000.0,1200.0);
INSERT INTO grains VALUES('OATS',1000.0,1200.0);
INSERT INTO grains VALUES('PEANUTS',1000.0,1200.0);
INSERT INTO grains VALUES('QUINOA',1000.0,1200.0);
INSERT INTO grains VALUES('RICE',200.0,250.0);
INSERT INTO grains VALUES('RYE',1000.0,1200.0);
INSERT INTO grains VALUES('SESAME',1000.0,1200.0);
INSERT INTO grains VALUES('SORGHUM',1000.0,1200.0);
INSERT INTO grains VALUES('SOY',300.0,350.0);
INSERT INTO grains VALUES('WHEAT',200.0,250.0);
CREATE TABLE processed_types(name TEXT, markup REAL, PRIMARY KEY (name)) WITHOUT ROWID;
INSERT INTO processed_types VALUES('BOILED',0.4000000000000000222);
INSERT INTO processed_types VALUES('BRAN',-0.29999999999999998889);
INSERT INTO processed_types VALUES('BRAN REMOVED',0.2000000000000000111);
INSERT INTO processed_types VALUES('DEHUSKED',0.14999999999999999444);
INSERT INTO processed_types VALUES('DRIED',0.4000000000000000222);
INSERT INTO processed_types VALUES('FLATBREAD',0.4000000000000000222);
INSERT INTO processed_types VALUES('FLAVOURED CANNED',0.4000000000000000222);
INSERT INTO processed_types VALUES('FLOUR',0.4000000000000000222);
INSERT INTO processed_types VALUES('GROUND',0.4000000000000000222);
INSERT INTO processed_types VALUES('HYDROGENATED OIL',0.4000000000000000222);
INSERT INTO processed_types VALUES('MILK',0.4000000000000000222);
INSERT INTO processed_types VALUES('MILK POWDER',3.0);
INSERT INTO processed_types VALUES('NOODLES',2.0);
INSERT INTO processed_types VALUES('OIL',3.0);
INSERT INTO processed_types VALUES('PAPER',2.0);
INSERT INTO processed_types VALUES('PASTA',2.0);
INSERT INTO processed_types VALUES('POP',0.4000000000000000222);
INSERT INTO processed_types VALUES('RBD OIL',3.2000000000000001776);
INSERT INTO processed_types VALUES('SNACK',0.4000000000000000222);
INSERT INTO processed_types VALUES('SPAGHETTI',2.0);
INSERT INTO processed_types VALUES('STICK',2.0);
INSERT INTO processed_types VALUES('SWEETENED',0.4000000000000000222);
INSERT INTO processed_types VALUES('UNPROCESSED',0.0);
CREATE TABLE weights (weight TEXT, units REAL, PRIMARY KEY (weight)) WITHOUT ROWID;
INSERT INTO weights VALUES('100g',0.10000000000000000555);
INSERT INTO weights VALUES('10kg',10.0);
INSERT INTO weights VALUES('1kg',1.0);
INSERT INTO weights VALUES('250g',0.25);
INSERT INTO weights VALUES('2kg',2.0);
INSERT INTO weights VALUES('500g',0.5);
INSERT INTO weights VALUES('50g',0.050000000000000002775);
INSERT INTO weights VALUES('5kg',5.0);
CREATE TABLE packaging (name TEXT, surcharge REAL, PRIMARY KEY (name)) WITHOUT ROWID;
INSERT INTO packaging VALUES('BAG',10.0);
INSERT INTO packaging VALUES('BOTTLED',10.0);
INSERT INTO packaging VALUES('BOXED',10.0);
INSERT INTO packaging VALUES('CANNED',10.0);
INSERT INTO packaging VALUES('PACKET',10.0);
INSERT INTO packaging VALUES('SACK',10.0);
INSERT INTO packaging VALUES('ZIPPER BAG',10.0);
CREATE TABLE flavouring (name TEXT, markup REAL, PRIMARY KEY (name)) WITHOUT ROWID;
INSERT INTO flavouring VALUES('CHILLI FLAVOURED',0.10000000000000000555);
INSERT INTO flavouring VALUES('CINNAMON',0.10000000000000000555);
INSERT INTO flavouring VALUES('COCOA',0.10000000000000000555);
INSERT INTO flavouring VALUES('GARLIC',0.10000000000000000555);
INSERT INTO flavouring VALUES('GINGER',0.10000000000000000555);
INSERT INTO flavouring VALUES('LEMON',0.10000000000000000555);
INSERT INTO flavouring VALUES('MIXED MASALA',0.11999999999999999555);
INSERT INTO flavouring VALUES('ONION',0.10000000000000000555);
INSERT INTO flavouring VALUES('OREGANO',0.10000000000000000555);
INSERT INTO flavouring VALUES('PERI PERI',0.10000000000000000555);
INSERT INTO flavouring VALUES('ROSE',0.10000000000000000555);
INSERT INTO flavouring VALUES('ROSEMARY',0.10000000000000000555);
INSERT INTO flavouring VALUES('SALTED',0.010000000000000000208);
INSERT INTO flavouring VALUES('SWEET',0.10000000000000000555);
INSERT INTO flavouring VALUES('UNFALVOURED',0.0);
INSERT INTO flavouring VALUES('VANILLA',0.10000000000000000555);
INSERT INTO flavouring VALUES('VINEGAR',0.10000000000000000555);
CREATE TABLE products(desc TEXT, itemcode INT, sell NUMERIC, cost NUMERIC, PRIMARY KEY (itemcode)) WITHOUT ROWID;
ANALYZE sqlite_schema;
INSERT INTO sqlite_stat1 VALUES('flavouring','flavouring','17 1');
INSERT INTO sqlite_stat1 VALUES('packaging','packaging','7 1');
INSERT INTO sqlite_stat1 VALUES('weights','weights','8 1');
INSERT INTO sqlite_stat1 VALUES('processed_types','processed_types','23 1');
INSERT INTO sqlite_stat1 VALUES('grains','grains','20 1');
CREATE INDEX covering_products ON products(itemcode, desc, cost, sell);
COMMIT;
INSERT INTO products(desc, cost, sell, itemcode) SELECT *, row_number() OVER () as itemcode FROM (SELECT grains.name || " " ||processed_types.name || " " || weights.weight || " " || packaging.name AS desc, cost * (1+processed_types.markup) * (weights.units) * (1+flavouring.markup) + packaging.surcharge AS cost, sell * (1+processed_types.markup) * (weights.units) * (1+flavouring.markup) + packaging.surcharge AS sell FROM grains JOIN processed_types JOIN weights JOIN packaging JOIN flavouring ORDER BY RANDOM() ) LIMIT 200000;
INSERT INTO full_inventory_current SELECT itemcode, sell, cost FROM products;
INSERT INTO cost_purchase SELECT itemcode, ABS(RANDOM()%100), date('now', '-10 days'), cost FROM products;
INSERT INTO sih_current SELECT itemcode, desc, ABS(RANDOM()%1000), cost, sell FROM products;
INSERT INTO hourly SELECT * FROM (WITH randomdates AS (SELECT x AS date FROM dates WHERE x BETWEEN datetime('now', '-730 days') AND datetime('now')), madeup AS (SELECT itemcode, randomdates.date, 10+ABS(RANDOM())%10 AS timehour, ABS(RANDOM())%100 AS qty, cost, sell FROM randomdates, products WHERE ABS(RANDOM()%50)=1) SELECT itemcode, date as daydate, timehour, qty AS quantity, sell*qty AS sumsell, cost*qty AS sumcost FROM madeup) LIMIT 10000000;
