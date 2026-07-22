$ErrorActionPreference = 'Stop'
$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Set-Location $ProjectRoot
$Failures = @()

Write-Host '=== GARDA 01 Phase 1 Runtime Check ===' -ForegroundColor Cyan
php -v

$RequiredExtensions = @(
    'fileinfo',
    'gd',
    'intl',
    'mysqli',
    'mbstring',
    'zip'
)

$Modules = php -m
foreach ($Extension in $RequiredExtensions) {
    if ($Modules -contains $Extension) {
        Write-Host "[OK] PHP extension: $Extension" -ForegroundColor Green
    } else {
        Write-Host "[MISSING] PHP extension: $Extension" -ForegroundColor Red
        $Failures += "PHP extension $Extension"
    }
}

$Files = @(
    'app\Libraries\SecureUploadService.php',
    'app\Filters\SecurityHeadersFilter.php',
    'app\Filters\InternalNoIndexFilter.php',
    'public\uploads\.htaccess',
    'app\Views\activities\quality.php',
    'app\Database\Migrations\2026-07-22-070000_AddMissingLocationSiteSettings.php'
)

foreach ($File in $Files) {
    if (Test-Path $File) {
        Write-Host "[OK] $File" -ForegroundColor Green
    } else {
        Write-Host "[MISSING] $File" -ForegroundColor Red
        $Failures += $File
    }
}

$DangerousExtensions = @(
    '*.php', '*.php3', '*.php4', '*.php5', '*.php7', '*.php8',
    '*.phtml', '*.phar', '*.cgi', '*.pl', '*.py', '*.sh',
    '*.exe', '*.bat', '*.cmd'
)

$DangerousFiles = @()
if (Test-Path 'public\uploads') {
    foreach ($Pattern in $DangerousExtensions) {
        $DangerousFiles += Get-ChildItem `
            -Path 'public\uploads' `
            -Recurse `
            -File `
            -Filter $Pattern `
            -ErrorAction SilentlyContinue
    }
}

if ($DangerousFiles.Count -gt 0) {
    Write-Host '[MISSING] Ditemukan file berbahaya di public/uploads:' -ForegroundColor Red
    $DangerousFiles.FullName | ForEach-Object { Write-Host "  $_" }
    $Failures += 'File berbahaya pada uploads'
} else {
    Write-Host '[OK] Tidak ditemukan ekstensi executable pada uploads.' -ForegroundColor Green
}

Write-Host "memory_limit: $(php -r 'echo ini_get("memory_limit");')"
Write-Host "upload_max_filesize: $(php -r 'echo ini_get("upload_max_filesize");')"
Write-Host "post_max_size: $(php -r 'echo ini_get("post_max_size");')"

if (Test-Path '.env') {
    $EnvironmentLine = Select-String `
        -Path '.env' `
        -Pattern '^CI_ENVIRONMENT\s*=' `
        -ErrorAction SilentlyContinue

    Write-Host "Environment: $EnvironmentLine"
} else {
    Write-Host '[WARNING] .env tidak ditemukan.' -ForegroundColor Yellow
}

php spark migrate:status
if ($LASTEXITCODE -ne 0) {
    $Failures += 'migrate:status'
}

php spark routes | findstr /I "activities/quality"
if ($LASTEXITCODE -ne 0) {
    $Failures += 'route activities/quality'
}

php spark filter:check get /activities/quality
if ($LASTEXITCODE -ne 0) {
    $Failures += 'filter activities/quality'
}

if ($Failures.Count -gt 0) {
    Write-Host "Runtime check belum lulus: $($Failures -join ', ')" -ForegroundColor Red
    exit 1
}

Write-Host 'Runtime check selesai tanpa temuan kritis.' -ForegroundColor Cyan
