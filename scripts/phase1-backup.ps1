param(
    [string]$Database = 'db_civicyouth',
    [string]$DbUser = 'root',
    [string]$DbHost = '127.0.0.1',
    [int]$DbPort = 3306,
    [string]$DbPassword = '',
    [string]$MySqlDump = 'C:\xampp\mysql\bin\mysqldump.exe'
)

$ErrorActionPreference = 'Stop'
$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Set-Location $ProjectRoot

$Timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$BackupRoot = Join-Path $ProjectRoot "backups\phase1-$Timestamp"
New-Item -ItemType Directory -Force -Path $BackupRoot | Out-Null

$Manifest = @()
$Manifest += "CreatedAt=$((Get-Date).ToString('o'))"
$Manifest += "ProjectRoot=$ProjectRoot"

if (Get-Command git -ErrorAction SilentlyContinue) {
    $Manifest += "GitBranch=$(git branch --show-current)"
    $Manifest += "GitCommit=$(git rev-parse HEAD)"
    git status --short | Set-Content (Join-Path $BackupRoot 'git-status.txt')
    git diff | Set-Content (Join-Path $BackupRoot 'working-tree.diff')
}

$SourceArchive = Join-Path $BackupRoot 'source-before-phase1.zip'
$SourcePaths = @('app', 'public\assets', 'public\.htaccess', 'public\robots.txt', 'docs') | Where-Object { Test-Path $_ }
Compress-Archive -Path $SourcePaths -DestinationPath $SourceArchive -Force

if (Test-Path 'public\uploads') {
    Compress-Archive -Path 'public\uploads' -DestinationPath (Join-Path $BackupRoot 'uploads-before-phase1.zip') -Force
}

if (!(Test-Path $MySqlDump)) {
    throw "mysqldump tidak ditemukan: $MySqlDump"
}

$SqlFile = Join-Path $BackupRoot 'database-before-phase1.sql'
$PreviousMySqlPassword = $env:MYSQL_PWD

try {
    if ($DbPassword -ne '') {
        $env:MYSQL_PWD = $DbPassword
    }

    & $MySqlDump `
        --host=$DbHost `
        --port=$DbPort `
        --user=$DbUser `
        --default-character-set=utf8mb4 `
        --single-transaction `
        --routines `
        --events `
        --result-file=$SqlFile `
        $Database
} finally {
    $env:MYSQL_PWD = $PreviousMySqlPassword
}

if ($LASTEXITCODE -ne 0 -or !(Test-Path $SqlFile) -or (Get-Item $SqlFile).Length -lt 100) {
    throw 'Backup database gagal atau file SQL tidak valid.'
}

$Manifest | Set-Content (Join-Path $BackupRoot 'manifest.txt')
Write-Host "Backup Fase 1 selesai: $BackupRoot" -ForegroundColor Green
