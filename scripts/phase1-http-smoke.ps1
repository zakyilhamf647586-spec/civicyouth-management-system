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

function Assert-ContentMatch {
    param(
        $Response,
        [string]$ExpectedPattern,
        [string]$Label
    )

    if ([string]$Response.Content -match $ExpectedPattern) {
        Write-Host "[OK] $Label" -ForegroundColor Green
    } else {
        Write-Host "[FAIL] $Label" -ForegroundColor Red
        $script:Failures += $Label
    }
}

function Assert-ContentNotMatch {
    param(
        $Response,
        [string]$UnexpectedPattern,
        [string]$Label
    )

    if ([string]$Response.Content -notmatch $UnexpectedPattern) {
        Write-Host "[OK] $Label" -ForegroundColor Green
    } else {
        Write-Host "[FAIL] $Label" -ForegroundColor Red
        $script:Failures += $Label
    }
}

function Assert-ContentDecodedMatch {
    param(
        $Response,
        [string]$ExpectedPattern,
        [string]$Label
    )

    $DecodedContent = [System.Net.WebUtility]::HtmlDecode(
        [string]$Response.Content
    )

    if ($DecodedContent -match $ExpectedPattern) {
        Write-Host "[OK] $Label" -ForegroundColor Green
    } else {
        Write-Host "[FAIL] $Label" -ForegroundColor Red
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
Assert-ContentMatch `
    $PublicHome `
    'hreflang="en"' `
    'Beranda memuat alternate English'
Assert-ContentMatch `
    $PublicHome `
    'public-preferences\.css' `
    'Beranda memuat kontrol bahasa dan tema'
Assert-ContentNotMatch `
    $PublicHome `
    '(?:href|action)="[^"]*/index\.php(?:/|\?|\#|\")' `
    'Beranda menghasilkan URL internal tanpa index.php'

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

$EnglishPaths = @(
    '/en',
    '/en/home',
    '/en/about',
    '/en/programs',
    '/en/activities',
    '/en/team',
    '/en/contact'
)
$EnglishResponses = @{}

foreach ($EnglishPath in $EnglishPaths) {
    $EnglishPage = Get-Page $EnglishPath
    $EnglishResponses[$EnglishPath] = $EnglishPage
    Assert-Status $EnglishPage 200 "English $EnglishPath"
    Assert-ContentMatch `
        $EnglishPage `
        '<html[^>]+lang="en"' `
        "Language marker $EnglishPath"
    Assert-HeaderNotMatch `
        $EnglishPage `
        'X-Robots-Tag' `
        'noindex' `
        "Indexability $EnglishPath"
}

Assert-ContentMatch `
    $EnglishResponses['/en'] `
    'In a small community, impact does not have to wait' `
    'Introducing English menerjemahkan narasi multiline'
Assert-ContentNotMatch `
    $EnglishResponses['/en'] `
    '>\s*Di lingkungan kecil,' `
    'Introducing English tidak menyisakan narasi Indonesia'

Assert-ContentMatch `
    $EnglishResponses['/en/programs'] `
    'Each pillar has a clear focus' `
    'Programs English menerjemahkan pengantar pilar'
Assert-ContentMatch `
    $EnglishResponses['/en/programs'] `
    'United(?:\s|\u2022|&bull;|&#8226;)*In Motion(?:\s|\u2022|&bull;|&#8226;)*Making an Impact' `
    'Footer English menerjemahkan slogan organisasi'

Assert-ContentDecodedMatch `
    $EnglishResponses['/en/home'] `
    'aria-label="Return to the [^"]+ Introducing experience"' `
    'Label aksesibilitas brand mengikuti bahasa English'
Assert-ContentDecodedMatch `
    $EnglishResponses['/en/home'] `
    'aria-label="Open the [^"]+ location in Google Maps"' `
    'Label aksesibilitas peta mengikuti bahasa English'
Assert-ContentMatch `
    $EnglishResponses['/en/home'] `
    'fetchpriority="high"' `
    'Gambar utama Beranda memiliki prioritas pemuatan'

Assert-ContentMatch `
    $EnglishResponses['/en/activities'] `
    'Programs, events, and activity documentation' `
    'Activities English menerjemahkan judul dan pengantar'
Assert-ContentNotMatch `
    $EnglishResponses['/en/activities'] `
    '>\s*(Peduli|Hijau|Belajar|Berkah|Sosialisasi|Kerja Bakti RW 01)\s*<|Kita bersama-sama melukis' `
    'Activities English menerjemahkan label dan data terdaftar'

Assert-ContentMatch `
    $EnglishResponses['/en/team'] `
    'Meet the team responsible for coordination' `
    'Team English menerjemahkan pengantar pengurus'
Assert-ContentMatch `
    $EnglishResponses['/en/team'] `
    'officials-chart-node-primary[\s\S]*?>\s*Chair\s*<[\s\S]*?officials-chart-grid[\s\S]*?>\s*Secretary\s*<' `
    'Hierarki Team English mempertahankan Chair sebelum core team'
Assert-ContentNotMatch `
    $EnglishResponses['/en/team'] `
    '>\s*(Seksi Olahraga|Inti|Tampan dan Berani|Koordinator Utama)\s*<' `
    'Team English menerjemahkan jabatan, divisi, dan profil'
Assert-ContentDecodedMatch `
    $EnglishResponses['/en/team'] `
    'aria-label="Show team member 1"' `
    'Label carousel Team mengikuti bahasa English'

foreach ($EnglishDatePath in @(
    '/en/home',
    '/en/activities'
)) {
    Assert-ContentNotMatch `
        $EnglishResponses[$EnglishDatePath] `
        '>\s*\d{2}\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4}\s*<' `
        "Tanggal dinamis $EnglishDatePath mengikuti bahasa English"
}

$EnglishActivityIds = @(
    [regex]::Matches(
        [string]$EnglishResponses['/en/activities'].Content,
        'href="[^"]*/en/activities/(\d+)"'
    ) |
        ForEach-Object { $_.Groups[1].Value } |
        Select-Object -Unique
)

$EnglishActivityDetail = $null
$EnglishGalleryDetail = $null

foreach ($EnglishActivityId in $EnglishActivityIds) {
    $CandidateActivityDetail = Get-Page "/en/activities/$EnglishActivityId"

    if ($null -eq $EnglishActivityDetail) {
        $EnglishActivityDetail = $CandidateActivityDetail
    }

    $DecodedCandidateContent = [System.Net.WebUtility]::HtmlDecode(
        [string]$CandidateActivityDetail.Content
    )

    if ($DecodedCandidateContent -match 'id="publicGalleryClose"') {
        $EnglishGalleryDetail = $CandidateActivityDetail
        break
    }
}

if ($null -ne $EnglishActivityDetail) {
    Assert-Status `
        $EnglishActivityDetail `
        200 `
        'English activity detail'
    Assert-ContentNotMatch `
        $EnglishActivityDetail `
        '>\s*\d{2}\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4}\s*<' `
        'Tanggal detail kegiatan mengikuti bahasa English'
} else {
    Write-Host '[WARN] Detail kegiatan English dilewati karena belum ada kegiatan yang dapat diperiksa.' -ForegroundColor Yellow
}

if ($null -ne $EnglishGalleryDetail) {
    Assert-ContentDecodedMatch `
        $EnglishGalleryDetail `
        'id="publicGalleryLightbox"[\s\S]*?role="dialog"[\s\S]*?aria-modal="true"' `
        'Galeri kegiatan menggunakan dialog aksesibel'
    Assert-ContentDecodedMatch `
        $EnglishGalleryDetail `
        'id="publicGalleryClose"[\s\S]*?aria-label="Close gallery"' `
        'Kontrol galeri mengikuti bahasa English'
} else {
    Write-Host '[WARN] Pemeriksaan galeri dilewati karena belum ada kegiatan English dengan foto galeri.' -ForegroundColor Yellow
}

$EnglishLogin = Get-Page '/en/login'
Assert-Status $EnglishLogin 200 'English login'
Assert-Header `
    $EnglishLogin `
    'X-Robots-Tag' `
    'noindex' `
    'English login noindex header'

$Robots = Get-Page '/robots.txt'
Assert-Status $Robots 200 'robots.txt'

if ([string]$Robots.Content -match 'Disallow:\s+/activities') {
    Write-Host '[OK] robots.txt melindungi route internal.' -ForegroundColor Green
} else {
    Write-Host '[FAIL] robots.txt belum memuat aturan internal.' -ForegroundColor Red
    $Failures += 'robots.txt'
}

Assert-ContentMatch `
    $Robots `
    'Disallow:\s+/en/login' `
    'robots.txt melindungi English login'

$Sitemap = Get-Page '/sitemap.xml'
Assert-Status $Sitemap 200 'sitemap.xml'

if ([string]$Sitemap.Content -match '/home</loc>') {
    Write-Host '[OK] sitemap.xml memuat Beranda /home.' -ForegroundColor Green
} else {
    Write-Host '[FAIL] sitemap.xml belum memuat /home.' -ForegroundColor Red
    $Failures += 'sitemap.xml /home'
}

Assert-ContentMatch `
    $Sitemap `
    '/en/home</loc>' `
    'sitemap.xml memuat English home'
Assert-ContentMatch `
    $Sitemap `
    'hreflang="en"' `
    'sitemap.xml memuat alternate language'

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

        $ExpectedHealthReadyCode = if ($HealthReadyStatus -eq 'critical') {
            503
        } else {
            200
        }

        if ([int]$HealthReady.StatusCode -eq $ExpectedHealthReadyCode) {
            Write-Host `
                "[OK] Status HTTP health ready konsisten dengan payload." `
                -ForegroundColor Green
        } else {
            Write-Host `
                "[FAIL] Status HTTP health ready tidak konsisten. HTTP $($HealthReady.StatusCode), payload $HealthReadyStatus." `
                -ForegroundColor Red
            $Failures += 'Konsistensi health ready'
        }

        $ReadyPass = [int]($HealthReadyPayload.checks.pass)
        $ReadyWarning = [int]($HealthReadyPayload.checks.warning)
        $ReadyCritical = [int]($HealthReadyPayload.checks.critical)
        Write-Host `
            "[INFO] Health checks: $ReadyPass normal, $ReadyWarning warning, $ReadyCritical kritis." `
            -ForegroundColor DarkCyan

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
