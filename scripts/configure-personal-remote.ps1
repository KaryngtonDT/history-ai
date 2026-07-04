# Configure Lumen Personal Remote after Tailscale login.
# Usage: powershell -ExecutionPolicy Bypass -File scripts/configure-personal-remote.ps1

$ErrorActionPreference = "Stop"
$Tailscale = "C:\Program Files\Tailscale\tailscale.exe"
$ApiBase = "http://127.0.0.1:8000"
$RepoRoot = Split-Path -Parent $PSScriptRoot

function Get-LanIPv4 {
    Get-NetIPAddress -AddressFamily IPv4 |
        Where-Object {
            $_.IPAddress -notlike "127.*" -and
            $_.IPAddress -notlike "100.*" -and
            $_.IPAddress -notlike "169.254.*" -and
            $_.IPAddress -notlike "172.*" -and
            $_.InterfaceAlias -notlike "*Tailscale*"
        } |
        Sort-Object -Property InterfaceMetric |
        Select-Object -ExpandProperty IPAddress -First 1
}

function Get-HomeWifiSsid {
    # Hotspot SSIDs must not be treated as home LAN (e.g. phone hotspot "steve").
    $ExcludeHotspotSsids = @('steve')

    $line = netsh wlan show interfaces | Select-String "^\s*SSID\s*:" | Select-Object -First 1
    if ($null -eq $line) { return @() }
    $ssid = ($line -replace "^\s*SSID\s*:\s*", "").Trim()
    if ($ssid -eq "" -or $ssid -eq "N/A") { return @() }
    if ($ExcludeHotspotSsids -contains $ssid) { return @() }
    return @($ssid)
}

Write-Host "=== Lumen Personal Remote setup ===" -ForegroundColor Cyan

if (-not (Test-Path $Tailscale)) {
    Write-Error "Tailscale not found. Install from https://tailscale.com/download"
}

$status = & $Tailscale status 2>&1 | Out-String
if ($status -match "NeedsLogin|Logged out") {
    Write-Host "Tailscale: please sign in (opening app)..." -ForegroundColor Yellow
    Start-Process $Tailscale
    Write-Host "After login, run this script again."
    exit 1
}

$tsIp = (& $Tailscale ip -4 2>&1 | Select-Object -First 1).Trim()
if ($tsIp -notmatch "^\d+\.\d+\.\d+\.\d+$") {
    Write-Error "Could not read Tailscale IPv4. Output: $tsIp"
}

$lanIp = Get-LanIPv4
if (-not $lanIp) {
    Write-Warning "No LAN IPv4 found; keeping 127.0.0.1 for LAN URL"
    $lanIp = "127.0.0.1"
}

$ssids = Get-HomeWifiSsid
Write-Host "Tailscale IP : $tsIp"
Write-Host "LAN IP       : $lanIp"
Write-Host "Home Wi-Fi   : $($ssids -join ', ')"

$body = @{
    mode           = "auto"
    localhostUrl   = "http://127.0.0.1:8000"
    lanUrl         = "http://${lanIp}:8000"
    tailscaleUrl   = "http://${tsIp}:8000"
    homeWifiSsids  = $ssids
} | ConvertTo-Json

Invoke-RestMethod -Method Put -Uri "$ApiBase/api/shadow/mobile/connection" -ContentType "application/json" -Body $body | Out-Null

Write-Host "Connection profile saved to Lumen." -ForegroundColor Green

# Firewall rule (requires admin; ignore if already exists)
try {
    $existing = Get-NetFirewallRule -DisplayName "Lumen Docker 8000" -ErrorAction SilentlyContinue
    if (-not $existing) {
        New-NetFirewallRule -DisplayName "Lumen Docker 8000" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow -Profile Any | Out-Null
        Write-Host "Firewall rule added for TCP 8000." -ForegroundColor Green
    } else {
        Write-Host "Firewall rule already present."
    }
} catch {
    Write-Warning "Could not add firewall rule (run PowerShell as Administrator): $_"
}

Write-Host ""
Write-Host "Test from phone (4G + Tailscale ON):" -ForegroundColor Cyan
Write-Host "  http://${tsIp}:8000/health"
Write-Host ""
Write-Host "Lumen settings:" -ForegroundColor Cyan
Write-Host "  $ApiBase/settings/connections"
Write-Host "  $ApiBase/settings/server"

# Save template for re-run
$templatePath = Join-Path $RepoRoot "scripts/tailscale-connection.json"
$body | Set-Content -Path $templatePath -Encoding UTF8
