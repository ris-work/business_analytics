#!/bin/bash
cd /saru/db_dir
echo sample
pwsh sample.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read import.sql"
cd cost
echo cost
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
cd ..
cd cost_purchase
echo cost_purchase
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
cd ..
cd sih_import
echo sih
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
cd ..
cd selling
echo selling
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
cd ..
