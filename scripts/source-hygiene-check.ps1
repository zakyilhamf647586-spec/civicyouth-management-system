$ErrorActionPreference = 'Stop'
$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Set-Location $ProjectRoot

Write-Host '=== GARDA 01 Source Hygiene ===' -ForegroundColor Cyan

$Unexpected = Get-ChildItem -LiteralPath $ProjectRoot -File |
    Where-Object {
        $_.Name -like 'hell -ExecutionPolicy Bypass*' -or
        $_.Name -like 'owerShell -ExecutionPolicy Bypass*'
    }

if ($Unexpected) {
    Write-Host '[WARNING] Artefak terminal ditemukan:' -ForegroundColor Yellow
    $Unexpected | ForEach-Object {
        Write-Host ('- ' + $_.Name) -ForegroundColor Yellow
    }
    Write-Host 'Hapus setelah memastikan file tersebut bukan source.' -ForegroundColor Yellow
} else {
    Write-Host 'Tidak ada artefak terminal yang dikenal.' -ForegroundColor Green
}

if (Get-Command git -ErrorAction SilentlyContinue) {
    Write-Host ''
    Write-Host 'Git status:' -ForegroundColor Cyan
    git status --short
}
