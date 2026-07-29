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
New-Item -ItemType Directory -Path $LogDirectory -Force | Out-Null

$LogFile = Join-Path $LogDirectory 'scheduled-backup.log'

& php spark backup:create --tag scheduled *>> $LogFile
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

$Latest = Get-ChildItem `
    -Path (Join-Path $ProjectPath 'writable\backups') `
    -Filter 'garda01-backup-*.zip' `
    | Sort-Object LastWriteTime -Descending `
    | Select-Object -First 1

if ($null -ne $Latest) {
    & php spark backup:verify `
        --file $Latest.Name `
        *>> $LogFile
}

& php spark backup:prune *>> $LogFile
exit $LASTEXITCODE
