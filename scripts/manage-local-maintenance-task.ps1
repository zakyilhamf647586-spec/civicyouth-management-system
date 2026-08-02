[CmdletBinding(SupportsShouldProcess = $true)]
param(
    [ValidateSet('Status', 'Install', 'Remove', 'RunNow')]
    [string]$Action = 'Status',
    [string]$ProjectPath = '',
    [string]$TaskName = 'GARDA01 Local Maintenance',
    [ValidatePattern('^([01]\d|2[0-3]):[0-5]\d$')]
    [string]$DailyAt = '02:00',
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

$RunnerPath = Join-Path $ProjectPath 'scripts\run-scheduled-backup.ps1'

if (-not (Test-Path -LiteralPath $RunnerPath -PathType Leaf)) {
    throw "Runner maintenance tidak ditemukan: $RunnerPath"
}

function Get-MaintenanceTask {
    return Get-ScheduledTask `
        -TaskName $TaskName `
        -ErrorAction SilentlyContinue
}

if ($Action -eq 'Status') {
    $ExistingTask = Get-MaintenanceTask

    if ($null -eq $ExistingTask) {
        Write-Host 'Task maintenance belum dipasang.' -ForegroundColor Yellow
        exit 0
    }

    $TaskInfo = Get-ScheduledTaskInfo -TaskName $TaskName
    Write-Host "Task       : $TaskName" -ForegroundColor Cyan
    Write-Host "Status     : $($ExistingTask.State)"
    Write-Host "Jadwal berikut: $($TaskInfo.NextRunTime)"
    Write-Host "Hasil terakhir: $($TaskInfo.LastTaskResult)"
    exit 0
}

if ($Action -eq 'Remove') {
    $ExistingTask = Get-MaintenanceTask

    if ($null -eq $ExistingTask) {
        Write-Host 'Task maintenance memang belum terpasang.' -ForegroundColor Yellow
        exit 0
    }

    if ($PSCmdlet.ShouldProcess($TaskName, 'Hapus scheduled task')) {
        Unregister-ScheduledTask `
            -TaskName $TaskName `
            -Confirm:$false
        Write-Host 'Task maintenance dihapus.' -ForegroundColor Green
    }

    exit 0
}

if ($Action -eq 'RunNow') {
    if ($null -eq (Get-MaintenanceTask)) {
        throw 'Pasang task terlebih dahulu dengan -Action Install.'
    }

    if ($PSCmdlet.ShouldProcess($TaskName, 'Jalankan scheduled task sekarang')) {
        Start-ScheduledTask -TaskName $TaskName
        Write-Host 'Task maintenance mulai dijalankan.' -ForegroundColor Green
    }

    exit 0
}

$PhpCommand = Get-Command $PhpExecutable -ErrorAction Stop
$PowerShellCommand = Get-Command powershell.exe -ErrorAction Stop
$TriggerTime = [datetime]::Today.Add(
    [TimeSpan]::ParseExact($DailyAt, 'hh\:mm', $null)
)
$TaskArguments = @(
    '-NoProfile'
    '-ExecutionPolicy Bypass'
    '-File "' + $RunnerPath + '"'
    '-ProjectPath "' + $ProjectPath + '"'
    '-PhpExecutable "' + $PhpCommand.Source + '"'
    '-MaxBackupAgeHours ' + $MaxBackupAgeHours
) -join ' '

$TaskAction = New-ScheduledTaskAction `
    -Execute $PowerShellCommand.Source `
    -Argument $TaskArguments
$TaskTrigger = New-ScheduledTaskTrigger -Daily -At $TriggerTime
$TaskSettings = New-ScheduledTaskSettingsSet `
    -StartWhenAvailable `
    -MultipleInstances IgnoreNew

if ($PSCmdlet.ShouldProcess($TaskName, "Pasang task harian pukul $DailyAt")) {
    Register-ScheduledTask `
        -TaskName $TaskName `
        -Action $TaskAction `
        -Trigger $TaskTrigger `
        -Settings $TaskSettings `
        -Description 'Backup, verifikasi, retensi, dan health snapshot GARDA 01.' `
        -Force `
        | Out-Null

    Write-Host "Task maintenance aktif setiap pukul $DailyAt." -ForegroundColor Green
    Write-Host 'Task berjalan untuk akun Windows yang sedang digunakan.'
}
