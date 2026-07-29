param(
    [switch]$IncludeVendor,
    [switch]$SkipPreflight,
    [switch]$AllowDirty
)

$ErrorActionPreference = 'Stop'
$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Set-Location $ProjectRoot

$Timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$ReleaseName = "garda01-production-$Timestamp"
$BuildRoot = Join-Path $ProjectRoot 'builds'
$StageRoot = Join-Path $BuildRoot $ReleaseName
$ZipPath = Join-Path $BuildRoot "$ReleaseName.zip"

New-Item -ItemType Directory -Path $BuildRoot -Force | Out-Null

if (!$AllowDirty -and (Get-Command git -ErrorAction SilentlyContinue)) {
    if (git status --porcelain) {
        throw 'Working tree belum bersih. Commit atau gunakan -AllowDirty secara sadar.'
    }
}

if (!$SkipPreflight) {
    powershell -ExecutionPolicy Bypass `
        -File (Join-Path $PSScriptRoot 'production-preflight.ps1')

    if ($LASTEXITCODE -ne 0) {
        throw 'Production preflight belum lulus.'
    }
}

if (Test-Path $StageRoot) {
    Remove-Item $StageRoot -Recurse -Force
}

New-Item -ItemType Directory -Path $StageRoot | Out-Null

$ExcludedTop = @(
    '.git', '.idea', '.vscode', 'backups',
    'builds', 'tests', '_rbac-review-source'
)

if (!$IncludeVendor) {
    $ExcludedTop += 'vendor'
}

function Test-ExcludedPath {
    param(
        [string]$RelativePath,
        [bool]$IsDirectory
    )

    $Normalized = $RelativePath.Replace('\', '/')
    $Segments = $Normalized.Split('/')

    if ($Segments.Count -gt 0 -and $ExcludedTop -contains $Segments[0]) {
        return $true
    }

    if (!$IsDirectory -and $Normalized -in @('.env', 'php_errors.log')) {
        return $true
    }

    foreach ($Prefix in @(
        'writable/cache/',
        'writable/logs/',
        'writable/session/',
        'writable/debugbar/',
        'writable/uploads/',
        'writable/backups/',
        'writable/backup-temp/'
    )) {
        if ($Normalized.StartsWith($Prefix, [System.StringComparison]::OrdinalIgnoreCase)) {
            $Name = Split-Path $Normalized -Leaf
            return !($Name -in @('index.html', '.htaccess', '.gitkeep'))
        }
    }

    if ($Normalized.StartsWith('public/uploads/', [System.StringComparison]::OrdinalIgnoreCase)) {
        $Name = Split-Path $Normalized -Leaf
        return !($Name -in @('.htaccess', '.gitkeep', '.gitignore', 'index.html'))
    }

    return $false
}

Get-ChildItem -Path $ProjectRoot -Force -Recurse | ForEach-Object {
    $Relative = $_.FullName.Substring($ProjectRoot.Length).TrimStart('\', '/')

    if (
        $Relative -eq ''
        -or (Test-ExcludedPath -RelativePath $Relative -IsDirectory $_.PSIsContainer)
    ) {
        return
    }

    $Destination = Join-Path $StageRoot $Relative

    if ($_.PSIsContainer) {
        New-Item -ItemType Directory -Path $Destination -Force | Out-Null
        return
    }

    New-Item -ItemType Directory -Path (Split-Path $Destination -Parent) -Force | Out-Null
    Copy-Item $_.FullName $Destination -Force
}

$Manifest = [ordered]@{
    release = $ReleaseName
    generated_at = (Get-Date).ToString('o')
    include_vendor = [bool]$IncludeVendor
    source_commit = ''
}

if (Get-Command git -ErrorAction SilentlyContinue) {
    $Manifest.source_commit = git rev-parse HEAD 2>$null
}

$Manifest | ConvertTo-Json -Depth 5 |
    Set-Content (Join-Path $StageRoot 'DEPLOYMENT-MANIFEST.json') -Encoding UTF8

if (Test-Path $ZipPath) {
    Remove-Item $ZipPath -Force
}

Compress-Archive `
    -Path (Join-Path $StageRoot '*') `
    -DestinationPath $ZipPath `
    -CompressionLevel Optimal

$Hash = (Get-FileHash $ZipPath -Algorithm SHA256).Hash.ToLowerInvariant()

Set-Content `
    -Path "$ZipPath.sha256.txt" `
    -Value "$Hash  $([System.IO.Path]::GetFileName($ZipPath))" `
    -Encoding ASCII

Remove-Item $StageRoot -Recurse -Force

Write-Host "Package: $ZipPath" -ForegroundColor Green
Write-Host "SHA-256: $Hash" -ForegroundColor Green
