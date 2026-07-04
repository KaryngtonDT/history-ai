# Opens inbound TCP 8000 for Lumen (LAN + Tailscale). Run as Administrator.
# Usage: powershell -ExecutionPolicy Bypass -File scripts/add-lumen-firewall.ps1

$ErrorActionPreference = "Stop"
$RuleName = "Lumen Docker 8000"

$existing = Get-NetFirewallRule -DisplayName $RuleName -ErrorAction SilentlyContinue
if ($existing) {
    Set-NetFirewallRule -DisplayName $RuleName -Profile Any -Enabled True | Out-Null
    Write-Host "Updated firewall rule '$RuleName' (Profile: Any)." -ForegroundColor Green
} else {
    New-NetFirewallRule `
        -DisplayName $RuleName `
        -Direction Inbound `
        -LocalPort 8000 `
        -Protocol TCP `
        -Action Allow `
        -Profile Any | Out-Null
    Write-Host "Created firewall rule '$RuleName' (Profile: Any)." -ForegroundColor Green
}

Write-Host "Test Tailscale from tablet: http://$( & 'C:\Program Files\Tailscale\tailscale.exe' ip -4 | Select-Object -First 1 ):8000/health"
