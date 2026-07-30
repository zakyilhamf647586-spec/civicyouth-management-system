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
            -UseBasicParsing `
            -ErrorAction Stop
    } catch {
        if ($_.Exception.Response) {
            $ErrorResponse = $_.Exception.Response
            $Reader = [System.IO.StreamReader]::new(
                $ErrorResponse.GetResponseStream()
            )

            try {
                $Content = $Reader.ReadToEnd()
            } finally {
                $Reader.Dispose()
            }

            return [PSCustomObject]@{
                StatusCode = [int]$ErrorResponse.StatusCode
                Headers = $ErrorResponse.Headers
                Content = $Content
            }
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
        Write-Host "[OK] ${Label}: $Value" -ForegroundColor Green
    } else {
        Write-Host "[FAIL] $Label. Nilai: $Value" -ForegroundColor Red
        $script:Failures += $Label
    }
}

function Assert-HeaderNotMatch {
    param(
        $Response,
        [string]$Header,
        [string]$UnexpectedPattern,
        [string]$Label
    )

    $Value = [string]$Response.Headers[$Header]

    if ($Value -notmatch $UnexpectedPattern) {
        Write-Host "[OK] $Label" -ForegroundColor Green
    } else {
        Write-Host "[FAIL] $Label. Nilai: $Value" -ForegroundColor Red
        $script:Failures += $Label
    }
}

function Assert-Status {
    param(
        $Response,
        [int]$ExpectedStatus,
        [string]$Label
    )

    if ([int]$Response.StatusCode -eq $ExpectedStatus) {
        Write-Host "[OK] $Label merespons $ExpectedStatus." -ForegroundColor Green
    } else {
        Write-Host "[FAIL] ${Label}: $($Response.StatusCode)" -ForegroundColor Red
        $script:Failures += $Label
    }
}

function Assert-StatusIn {
    param(
        $Response,
        [int[]]$ExpectedStatuses,
        [string]$Label
    )

    $ActualStatus = [int]$Response.StatusCode

    if ($ExpectedStatuses -contains $ActualStatus) {
        Write-Host "[OK] $Label merespons $ActualStatus." -ForegroundColor Green
    } else {
        $Expected = $ExpectedStatuses -join ' atau '
        Write-Host "[FAIL] ${Label}: $ActualStatus; diharapkan $Expected." -ForegroundColor Red
        $script:Failures += $Label
    }
}

Write-Host '=== GARDA 01 Phase 1 HTTP Smoke Test ===' -ForegroundColor Cyan

$Introducing = Get-Page '/'
Assert-Status $Introducing 200 'Introducing'

$PublicHome = Get-Page '/home'
Assert-Status $PublicHome 200 'Beranda publik'

Assert-Header $PublicHome 'X-Content-Type-Options' '^nosniff$' 'Security header nosniff'
Assert-Header $PublicHome 'X-Frame-Options' '^SAMEORIGIN$' 'Security header frame'
Assert-HeaderNotMatch $PublicHome 'X-Robots-Tag' 'noindex' 'Beranda dapat diindeks'

$Login = Get-Page '/login'
Assert-Header $Login 'X-Robots-Tag' 'noindex' 'Login noindex header'

foreach ($PublicPath in @(
    '/profil',
    '/program',
    '/kegiatan',
    '/pengurus',
    '/kontak'
)) {
    $PublicPage = Get-Page $PublicPath
    Assert-Status $PublicPage 200 "Public $PublicPath"
    Assert-HeaderNotMatch `
        $PublicPage `
        'X-Robots-Tag' `
        'noindex' `
        "Indexability $PublicPath"
}

$Robots = Get-Page '/robots.txt'
Assert-Status $Robots 200 'robots.txt'

if ([string]$Robots.Content -match 'Disallow:\s+/activities') {
    Write-Host '[OK] robots.txt melindungi route internal.' -ForegroundColor Green
} else {
    Write-Host '[FAIL] robots.txt belum memuat aturan internal.' -ForegroundColor Red
    $Failures += 'robots.txt'
}

$Sitemap = Get-Page '/sitemap.xml'
Assert-Status $Sitemap 200 'sitemap.xml'

if ([string]$Sitemap.Content -match '/home</loc>') {
    Write-Host '[OK] sitemap.xml memuat Beranda /home.' -ForegroundColor Green
} else {
    Write-Host '[FAIL] sitemap.xml belum memuat /home.' -ForegroundColor Red
    $Failures += 'sitemap.xml /home'
}

$HealthLive = Get-Page '/health/live'
Assert-Status $HealthLive 200 'Health live'
Assert-Header $HealthLive 'X-Robots-Tag' 'noindex' 'Health live noindex header'

if ([string]$HealthLive.Content -match '"status"\s*:\s*"alive"') {
    Write-Host '[OK] Health live mengembalikan status alive.' -ForegroundColor Green
} else {
    Write-Host '[FAIL] Payload health live tidak valid.' -ForegroundColor Red
    $Failures += 'Health live payload'
}

$HealthReady = Get-Page '/health/ready'
Assert-StatusIn $HealthReady @(200, 503) 'Health ready endpoint'

try {
    $HealthReadyPayload = $HealthReady.Content | ConvertFrom-Json
    $HealthReadyStatus = [string]$HealthReadyPayload.status

    if (@('healthy', 'degraded', 'critical') -contains $HealthReadyStatus) {
        Write-Host "[OK] Health ready payload: $HealthReadyStatus." -ForegroundColor Green

        if ($HealthReadyStatus -eq 'critical') {
            Write-Host '[WARN] Sistem melaporkan kondisi critical; tinjau Operational Dashboard sebelum production.' -ForegroundColor Yellow
        }
    } else {
        Write-Host "[FAIL] Status health ready tidak dikenal: $HealthReadyStatus." -ForegroundColor Red
        $Failures += 'Health ready payload'
    }
} catch {
    Write-Host '[FAIL] Payload health ready bukan JSON yang valid.' -ForegroundColor Red
    $Failures += 'Health ready payload'
}

if ($Failures.Count -gt 0) {
    Write-Host "Smoke test gagal: $($Failures -join ', ')" -ForegroundColor Red
    exit 1
}

Write-Host 'Seluruh HTTP smoke test dasar berhasil.' -ForegroundColor Cyan
