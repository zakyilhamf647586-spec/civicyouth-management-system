param(
    [switch]$Strict = $true
)

$ErrorActionPreference = 'Stop'
$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Set-Location $ProjectRoot

Write-Host '=== GARDA 01 Production Preflight ===' -ForegroundColor Cyan

$Failures = @()

php -v
if ($LASTEXITCODE -ne 0) {
    $Failures += 'PHP'
}

if (Get-Command composer -ErrorAction SilentlyContinue) {
    composer validate --no-check-publish
    if ($LASTEXITCODE -ne 0) {
        $Failures += 'composer validate'
    }
} else {
    Write-Host '[WARNING] Composer tidak ditemukan.' -ForegroundColor Yellow
}

php spark migrate:status
if ($LASTEXITCODE -ne 0) {
    $Failures += 'migrate:status'
}

$Arguments = @('spark', 'production:check')
if ($Strict) {
    $Arguments += '--strict'
}

& php @Arguments
if ($LASTEXITCODE -ne 0) {
    $Failures += 'production:check'
}

if ($Failures.Count -gt 0) {
    Write-Host "Preflight gagal: $($Failures -join ', ')" -ForegroundColor Red
    exit 1
}

Write-Host 'Preflight lulus.' -ForegroundColor Green
