param(
    [string]$ProjectPath = (
        Resolve-Path (
            Join-Path $PSScriptRoot '..'
        )
    ).Path
)

$ErrorActionPreference = 'Stop'
Set-Location $ProjectPath

$LogDirectory = Join-Path $ProjectPath 'writable\logs'
$CacheDirectory = Join-Path $ProjectPath 'writable\cache'

New-Item -ItemType Directory -Path $LogDirectory -Force | Out-Null
New-Item -ItemType Directory -Path $CacheDirectory -Force | Out-Null

$LogFile = Join-Path $LogDirectory 'health-monitor.log'

& php spark system:health:snapshot *>> $LogFile
$SnapshotExit = $LASTEXITCODE

# Run retention at most once per day, not every five minutes.
$Today = Get-Date -Format 'yyyyMMdd'
$PruneMarker = Join-Path $CacheDirectory "health-prune-$Today.flag"

if (!(Test-Path $PruneMarker)) {
    & php spark system:health:prune --days 30 *>> $LogFile

    if ($LASTEXITCODE -eq 0) {
        Set-Content -Path $PruneMarker -Value (Get-Date).ToString('o')
    }

    Get-ChildItem -Path $CacheDirectory -Filter 'health-prune-*.flag' |
        Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-2) } |
        Remove-Item -Force -ErrorAction SilentlyContinue
}

exit $SnapshotExit
