#!/bin/bash
cd /saru/db_dir
pwsh sample.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read import.sql"
cd cost
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
cd ..
cd cost_purchase
pwsh cost.ps1
sqlite3 /saru/www-data/hourly.sqlite3 ".read cost.sql"
cd ..
