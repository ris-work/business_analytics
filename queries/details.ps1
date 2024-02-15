. /saru/auth.ps1
#tables.csv: has SELECT TABLE_NAME FROM information_schema.tables
#$query = Get-Content sih.sql
#$query_t = Get-Content sih_t.sql
$tables = Get-Content .\tables.csv | ConvertFrom-Csv
echo $tables
foreach($table in $tables){
#Get-Date -Format "o"
#Invoke-Sqlcmd -ServerInstance "127.0.0.1,21433" -Query "$query" -Encrypt "Optional" -TrustServerCertificate -User "pos" -Password "$cred" | ConvertTo-csv -NoHeader | Out-File -File sih.csv.inprogress && mv sih.csv.inprogress sih.csv
#Invoke-Sqlcmd -ServerInstance "127.0.0.1,21433" -Query "$query_t" -Encrypt "Optional" -TrustServerCertificate -User "pos" -Password "$cred" | ConvertTo-csv | Out-File -File sih_t.csv.inprogress && mv sih_t.csv.inprogress sih_t.csv
$name = $table.TABLE_NAME
Invoke-Sqlcmd -ServerInstance "127.0.0.1,21433" -Query "SELECT count(*), `'$name`' FROM $name" -Encrypt "Optional" -TrustServerCertificate -User "pos" -Password "$cred" | ConvertTo-csv -NoHeader | Out-File -Append -File size.csv.inprogress 
Get-Date -Format "o"
}
mv size.csv.inprogress size.csv

