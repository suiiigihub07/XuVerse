param(
    [Parameter(Mandatory = $true)]
    [ValidatePattern('^https://')]
    [string]$BaseUrl
)

$ErrorActionPreference = 'Stop'
$siteBase = $BaseUrl.TrimEnd('/')
$siteUri = [Uri]$siteBase
if (-not $siteUri.IsAbsoluteUri -or $siteUri.Scheme -ne 'https' -or $siteUri.Query -or $siteUri.Fragment) {
    throw 'BaseUrl must be an absolute HTTPS site URL without a query or fragment.'
}

$failures = [System.Collections.Generic.List[string]]::new()
$responses = @{}
$routes = @('/', '/resume', '/projects', '/media', '/articles', '/contact', '/sitemap.xml', '/robots.txt', '/resume/download')

function Read-LaunchResponse {
    param([string]$Url)
    try {
        $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -MaximumRedirection 3 -TimeoutSec 30
        return [pscustomobject]@{ Status = [int]$response.StatusCode; Response = $response; Error = '' }
    } catch {
        $status = 0
        if ($null -ne $_.Exception.Response) {
            $status = [int]$_.Exception.Response.StatusCode
        }
        return [pscustomobject]@{ Status = $status; Response = $null; Error = $_.Exception.Message }
    }
}

foreach ($route in $routes) {
    $result = Read-LaunchResponse ($siteBase + $route)
    if ($result.Status -ne 200) {
        $failures.Add("${route} returned $($result.Status): $($result.Error)")
        continue
    }

    $response = $result.Response
    $responses[$route] = $response
    Write-Host "PASS $route"

    if ($route -in @('/', '/resume', '/projects', '/media', '/articles', '/contact')) {
        $canonicalMatch = [regex]::Match($response.Content, '<link\b[^>]*rel=["'']canonical["''][^>]*href=["'']([^"'']+)', 'IgnoreCase')
        $expectedCanonical = $siteBase + $route
        if (-not $canonicalMatch.Success -or $canonicalMatch.Groups[1].Value -ne $expectedCanonical) {
            $failures.Add("${route} has a missing or incorrect canonical URL; expected $expectedCanonical")
        }
        if ($response.Content -match '<meta\b[^>]*name=["'']robots["''][^>]*content=["''][^"'']*noindex') {
            $failures.Add("${route} is marked noindex. Check XUVERSE_PRODUCTION.")
        }
        if (([string]$response.Headers['X-Robots-Tag']) -match 'noindex') {
            $failures.Add("${route} has an X-Robots-Tag noindex header.")
        }
        if ($response.Content -match '(?:https?:)?//(?:localhost|127\.0\.0\.1)(?::\d+)?(?:/|["''])') {
            $failures.Add("${route} contains a localhost URL.")
        }
        if ($siteUri.AbsolutePath.Trim('/') -eq '' -and $response.Content -match '(?:href|src)=["'']/xuverse/') {
            $failures.Add("${route} still links to the local /xuverse/ installation path.")
        }
    }
}

if ($responses.ContainsKey('/')) {
    $homepageResponse = $responses['/']
    foreach ($name in @('Content-Security-Policy', 'X-Content-Type-Options', 'X-Frame-Options', 'Referrer-Policy', 'Strict-Transport-Security')) {
        if ([string]::IsNullOrWhiteSpace([string]$homepageResponse.Headers[$name])) {
            $failures.Add("Missing required production header: $name")
        } else {
            Write-Host "PASS $name"
        }
    }
}

if ($responses.ContainsKey('/robots.txt')) {
    $robotsContent = [string]$responses['/robots.txt'].Content
    if ($robotsContent -match '(?m)^\s*Disallow:\s*/\s*$') {
        $failures.Add('robots.txt blocks the entire site. Check XUVERSE_PRODUCTION.')
    }
    if ($robotsContent -notmatch [regex]::Escape('Sitemap: ' + $siteBase + '/sitemap.xml')) {
        $failures.Add('robots.txt does not advertise the production sitemap URL.')
    }
}

if ($responses.ContainsKey('/sitemap.xml')) {
    try {
        [xml]$sitemapDocument = $responses['/sitemap.xml'].Content
        $locations = @($sitemapDocument.SelectNodes('//*[local-name()="loc"]'))
        if ($locations.Count -eq 0) {
            $failures.Add('Sitemap has no page URLs.')
        }
        foreach ($location in $locations) {
            if (-not $location.InnerText.StartsWith($siteBase + '/', [StringComparison]::OrdinalIgnoreCase)) {
                $failures.Add("Sitemap URL does not belong to this production site: $($location.InnerText)")
            }
        }
    } catch {
        $failures.Add("Sitemap is not valid XML: $($_.Exception.Message)")
    }
}

if ($responses.ContainsKey('/resume/download')) {
    $pdfResponse = $responses['/resume/download']
    if (([string]$pdfResponse.Headers['Content-Type']) -notmatch 'application/pdf') {
        $failures.Add('Resume download does not return a PDF.')
    }
}

$missingResult = Read-LaunchResponse ($siteBase + '/__xuverse-launch-check-missing__')
if ($missingResult.Status -ne 404) {
    $failures.Add("Unknown URLs must return 404; received $($missingResult.Status).")
}

foreach ($privatePath in @('/.env', '/database/xuverse.sql', '/includes/db.php', '/scripts/launch-preflight.ps1', '/vendor/autoload.php')) {
    $privateResult = Read-LaunchResponse ($siteBase + $privatePath)
    if ($privateResult.Status -notin @(403, 404)) {
        $failures.Add("Private path $privatePath must return 403 or 404; received $($privateResult.Status).")
    }
}

if ($failures.Count -gt 0) {
    foreach ($failure in $failures) {
        Write-Host "FAIL $failure" -ForegroundColor Red
    }
    throw "Launch preflight failed with $($failures.Count) issue(s)."
}

Write-Host 'Launch preflight passed.'
