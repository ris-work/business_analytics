#!/bin/bash
cd /saru/db_dir
echo sample
pwsh sample.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read import.sql"
rm sample.csv
cd cost
echo cost
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
rm cost.csv
cd ..
cd cost_purchase
echo cost_purchase
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
rm cost.csv
cd ..
cd sih_import
echo sih
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
rm cost.csv
cd ..
cd selling
echo selling
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
rm cost.csv
cd ..
cd prod_list
echo prod_list
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
rm cost.csv
cd ..
cd full_inventory_current
echo full_inventory_current
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
rm cost.csv
cd ..
cd vendor_details
echo vendor_details
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
rm cost.csv
cd ..
cd product_vendors
echo product_vendors
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
rm cost.csv
cd ..
cd barcode
echo barcode
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
rm cost.csv
cd ..
