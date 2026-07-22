param(
    [Parameter(Mandatory = $true)]
    [string]$BackupDirectory,
    [switch]$ConfirmRollback
)

$ErrorActionPreference = 'Stop'

if (!$ConfirmRollback) {
    throw 'Tambahkan -ConfirmRollback untuk mengonfirmasi rollback.'
}

$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$BackupDirectory = (Resolve-Path $BackupDirectory).Path
$SourceArchive = Join-Path $BackupDirectory 'source-before-phase1.zip'

if (!(Test-Path $SourceArchive)) {
    throw 'Arsip source backup tidak ditemukan.'
}

Set-Location $ProjectRoot
Expand-Archive -Path $SourceArchive -DestinationPath $ProjectRoot -Force

$NewFiles = @(
    'app\Libraries\SecureUploadService.php',
    'app\Filters\SecurityHeadersFilter.php',
    'app\Filters\InternalNoIndexFilter.php',
    'app\Database\Migrations\2026-07-22-070000_AddMissingLocationSiteSettings.php',
    'app\Views\activities\quality.php',
    'public\assets\css\admin-activity-quality.css',
    'public\uploads\.htaccess',
    'public\uploads\index.html',
    'docs\PHASE1-RUNTIME-STABILIZATION.md',
    'docs\PHASE1-ROLLBACK-MANIFEST.md',
    'docs\PHASE1-TEST-REPORT.md',
    'scripts\phase1-http-smoke.ps1'
)

foreach ($File in $NewFiles) {
    if (Test-Path $File) {
        Remove-Item $File -Force
    }
}

Write-Host 'Source sebelum Fase 1 telah dipulihkan.' -ForegroundColor Yellow
Write-Host 'Database dan uploads tidak dipulihkan otomatis demi mencegah kehilangan data baru.'
Write-Host 'Gunakan database-before-phase1.sql dan uploads-before-phase1.zip hanya bila benar-benar diperlukan.'
