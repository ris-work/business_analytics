. /saru/auth.ps1
$query = Get-Content query_cost.sql
#$query_t = Get-Content sih_t.sql
Get-Date -Format "o"
Invoke-Sqlcmd -ServerInstance "$serv" -Query "$query" -Encrypt "Optional" -TrustServerCertificate -User "pos" -Password "$cred" | ConvertTo-csv -NoHeader | Out-File -File cost.csv.inprogress && mv cost.csv.inprogress cost.csv
#Invoke-Sqlcmd -ServerInstance "127.0.0.1,21433" -Query "$query_t" -Encrypt "Optional" -TrustServerCertificate -User "pos" -Password "$cred" | ConvertTo-csv | Out-File -File sih_t.csv.inprogress && mv sih_t.csv.inprogress sih_t.csv
Get-Date -Format "o"
