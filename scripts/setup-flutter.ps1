# Adds Flutter to user PATH and runs doctor.
# Flutter SDK expected at C:\src\flutter (git clone stable).

$FlutterRoot = "C:\src\flutter"
$FlutterBin = Join-Path $FlutterRoot "bin"

if (-not (Test-Path (Join-Path $FlutterBin "flutter.bat"))) {
    Write-Host "Flutter not found at $FlutterRoot" -ForegroundColor Red
    Write-Host "Clone: git clone https://github.com/flutter/flutter.git -b stable --depth 1 C:\src\flutter"
    exit 1
}

$userPath = [Environment]::GetEnvironmentVariable("Path", "User")
if ($userPath -notlike "*$FlutterBin*") {
    [Environment]::SetEnvironmentVariable("Path", "$FlutterBin;$userPath", "User")
    Write-Host "Added $FlutterBin to user PATH (restart terminal)." -ForegroundColor Green
}

$env:Path = "$FlutterBin;" + $env:Path
flutter doctor
