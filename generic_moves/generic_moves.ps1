echo "pwd: $(pwd)"
. /etc/auth.ps1
$query = Get-Content query_generic_moves.sql
#$query_t = Get-Content sih_t.sql
Get-Date -Format "o"
Invoke-Sqlcmd -ServerInstance "$serv" -Query "$query" -Encrypt "Optional" -TrustServerCertificate -User "pos" -Password "$cred" | ConvertTo-csv -NoHeader | Out-File -File generic_moves.csv.inprogress && mv generic_moves.csv.inprogress generic_moves.csv
#Invoke-Sqlcmd -ServerInstance "127.0.0.1,21433" -Query "$query_t" -Encrypt "Optional" -TrustServerCertificate -User "pos" -Password "$cred" | ConvertTo-csv | Out-File -File sih_t.csv.inprogress && mv sih_t.csv.inprogress sih_t.csv
Get-Date -Format "o"
