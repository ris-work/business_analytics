echo "pwd: $(pwd)"
. /saru/auth.ps1
$query = Get-Content query_hourly.sql
Get-Date -Format "o"
Invoke-Sqlcmd -ServerInstance "$serv" -Query "$query" -Encrypt "Optional" -TrustServerCertificate -User "pos" -Password "$cred" | ConvertTo-csv -NoHeader | Out-File -File sample.csv.inprogress && mv sample.csv.inprogress sample.csv
Get-Date -Format "o"
