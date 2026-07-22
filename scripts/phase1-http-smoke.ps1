param(
    [string]$BaseUrl = 'http://127.0.0.1:8080'
)

$ErrorActionPreference = 'Stop'
$Failures = @()

function Get-Page {
    param([string]$Path)

    try {
        return Invoke-WebRequest `
            -Uri ($BaseUrl.TrimEnd('/') + $Path) `
            -MaximumRedirection 0 `
            -UseBasicParsing `
            -ErrorAction Stop
    } catch {
        if ($_.Exception.Response) {
            return $_.Exception.Response
        }

        throw
    }
}

function Assert-Header {
    param(
        $Response,
        [string]$Header,
        [string]$ExpectedPattern,
        [string]$Label
    )

    $Value = [string]$Response.Headers[$Header]

    if ($Value -match $ExpectedPattern) {
        Write-Host "[OK] $Label: $Value" -ForegroundColor Green
    } else {
        Write-Host "[FAIL] $Label. Nilai: $Value" -ForegroundColor Red
        $script:Failures += $Label
    }
}

Write-Host '=== GARDA 01 Phase 1 HTTP Smoke Test ===' -ForegroundColor Cyan

$PublicHome = Get-Page '/'
if ([int]$PublicHome.StatusCode -eq 200) {
    Write-Host '[OK] Beranda publik merespons 200.' -ForegroundColor Green
} else {
    Write-Host "[FAIL] Beranda: $($PublicHome.StatusCode)" -ForegroundColor Red
    $Failures += 'Beranda publik'
}

Assert-Header $PublicHome 'X-Content-Type-Options' '^nosniff$' 'Security header nosniff'
Assert-Header $PublicHome 'X-Frame-Options' '^SAMEORIGIN$' 'Security header frame'

$Login = Get-Page '/login'
Assert-Header $Login 'X-Robots-Tag' 'noindex' 'Login noindex header'

$Internal = Get-Page '/activities/quality'
Assert-Header $Internal 'X-Robots-Tag' 'noindex' 'Internal route noindex header'

$Officials = Get-Page '/pengurus'
if ([int]$Officials.StatusCode -eq 200) {
    Write-Host '[OK] Halaman Pengurus merespons 200.' -ForegroundColor Green
} else {
    Write-Host "[FAIL] Pengurus: $($Officials.StatusCode)" -ForegroundColor Red
    $Failures += 'Halaman Pengurus'
}

$Robots = Get-Page '/robots.txt'
if ([string]$Robots.Content -match 'Disallow:\s+/activities') {
    Write-Host '[OK] robots.txt melindungi route internal.' -ForegroundColor Green
} else {
    Write-Host '[FAIL] robots.txt belum memuat aturan internal.' -ForegroundColor Red
    $Failures += 'robots.txt'
}

if ($Failures.Count -gt 0) {
    Write-Host "Smoke test gagal: $($Failures -join ', ')" -ForegroundColor Red
    exit 1
}

Write-Host 'Seluruh HTTP smoke test dasar berhasil.' -ForegroundColor Cyan
