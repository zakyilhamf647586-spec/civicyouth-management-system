param(
    [string]$TaskName = 'GARDA01 Daily Backup',
    [string]$DailyTime = '02:00',
    [string]$ProjectPath = (
        Resolve-Path (
            Join-Path $PSScriptRoot '..'
        )
    ).Path
)

$ErrorActionPreference = 'Stop'

$Runner = Join-Path $ProjectPath 'scripts\run-scheduled-backup.ps1'

if (!(Test-Path $Runner)) {
    throw "Runner tidak ditemukan: $Runner"
}

$PowerShell = (Get-Command powershell.exe).Source

$Arguments = @(
    '-NoProfile',
    '-ExecutionPolicy', 'Bypass',
    '-File', "`"$Runner`"",
    '-ProjectPath', "`"$ProjectPath`""
) -join ' '

$Action = New-ScheduledTaskAction `
    -Execute $PowerShell `
    -Argument $Arguments `
    -WorkingDirectory $ProjectPath

$ParsedTime = [datetime]::ParseExact(
    $DailyTime,
    'HH:mm',
    $null
)

$Trigger = New-ScheduledTaskTrigger `
    -Daily `
    -At $ParsedTime

$Settings = New-ScheduledTaskSettingsSet `
    -StartWhenAvailable `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -ExecutionTimeLimit (
        New-TimeSpan -Hours 2
    )

Register-ScheduledTask `
    -TaskName $TaskName `
    -Action $Action `
    -Trigger $Trigger `
    -Settings $Settings `
    -Description 'Backup harian database dan upload GARDA 01.' `
    -Force

Write-Host `
    "Scheduled Task terpasang: $TaskName" `
    -ForegroundColor Green
