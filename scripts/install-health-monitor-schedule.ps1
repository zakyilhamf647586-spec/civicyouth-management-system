param(
    [string]$TaskName = 'GARDA01 Health Monitor',
    [int]$IntervalMinutes = 5,
    [string]$ProjectPath = (
        Resolve-Path (
            Join-Path $PSScriptRoot '..'
        )
    ).Path
)

$ErrorActionPreference = 'Stop'

if ($IntervalMinutes -lt 5) {
    throw 'Interval minimum yang disarankan adalah 5 menit.'
}

$Runner = Join-Path $ProjectPath 'scripts\run-health-monitor.ps1'

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

$Start = (Get-Date).AddMinutes(1)
$Trigger = New-ScheduledTaskTrigger `
    -Once `
    -At $Start `
    -RepetitionInterval (New-TimeSpan -Minutes $IntervalMinutes)

$Settings = New-ScheduledTaskSettingsSet `
    -StartWhenAvailable `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 4)

Register-ScheduledTask `
    -TaskName $TaskName `
    -Action $Action `
    -Trigger $Trigger `
    -Settings $Settings `
    -Description 'Snapshot kesehatan operasional GARDA 01.' `
    -Force

Write-Host "Scheduled Task terpasang: $TaskName" -ForegroundColor Green
