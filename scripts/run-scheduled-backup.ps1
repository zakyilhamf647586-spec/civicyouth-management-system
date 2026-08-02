param(
    [string]$ProjectPath = '',
    [string]$PhpExecutable = 'php',
    [ValidateRange(1, 720)]
    [int]$MaxBackupAgeHours = 24
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($ProjectPath)) {
    $ProjectPath = (
        Resolve-Path -LiteralPath (
            Join-Path $PSScriptRoot '..'
        )
    ).Path
} else {
    $ProjectPath = (
        Resolve-Path -LiteralPath $ProjectPath
    ).Path
}

Set-Location $ProjectPath

$LogDirectory = Join-Path $ProjectPath 'writable\logs'
New-Item -ItemType Directory -Path $LogDirectory -Force | Out-Null

$LogFile = Join-Path $LogDirectory 'scheduled-backup.log'
$StartedAt = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'

Add-Content `
    -LiteralPath $LogFile `
    -Value "`r`n[$StartedAt] Maintenance dimulai."

try {
    $PhpCommand = Get-Command $PhpExecutable -ErrorAction Stop
    $CommandArguments = @(
        'spark'
        'system:maintenance'
        "--max-age-hours=$MaxBackupAgeHours"
    )

    & $PhpCommand.Source @CommandArguments *>> $LogFile

    if ($LASTEXITCODE -ne 0) {
        throw "system:maintenance berhenti dengan kode $LASTEXITCODE."
    }

    $FinishedAt = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    Add-Content `
        -LiteralPath $LogFile `
        -Value "[$FinishedAt] Maintenance berhasil."

    Write-Host 'Maintenance GARDA 01 berhasil.' -ForegroundColor Green
    exit 0
} catch {
    $FailedAt = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    Add-Content `
        -LiteralPath $LogFile `
        -Value "[$FailedAt] Maintenance gagal: $($_.Exception.Message)"

    Write-Error $_
    exit 1
}
