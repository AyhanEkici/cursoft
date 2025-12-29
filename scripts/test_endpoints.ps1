# PowerShell Script to Test Cursoft API Endpoints
# Usage: .\scripts\test_endpoints.ps1

$baseUrl = "http://localhost/cursoft"

Write-Host "🧪 Testing Cursoft API Endpoints" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Test Health Endpoint
Write-Host "1. Testing Health Endpoint..." -ForegroundColor Yellow
try {
    $health = Invoke-RestMethod -Uri "$baseUrl/api/health.php" -Method Get
    Write-Host "✅ Health Check: $($health.status)" -ForegroundColor Green
    Write-Host "   Database: $($health.checks.database)" -ForegroundColor Gray
    Write-Host "   Disk Usage: $($health.checks.disk.usage_percent)%" -ForegroundColor Gray
    Write-Host "   PHP Version: $($health.checks.php.version)" -ForegroundColor Gray
} catch {
    Write-Host "❌ Health Check Failed: $_" -ForegroundColor Red
}
Write-Host ""

# Test Metrics Endpoint
Write-Host "2. Testing Metrics Endpoint..." -ForegroundColor Yellow
try {
    $metrics = Invoke-WebRequest -Uri "$baseUrl/api/metrics.php" -UseBasicParsing
    Write-Host "✅ Metrics Endpoint: OK" -ForegroundColor Green
    Write-Host "   Response Length: $($metrics.Content.Length) bytes" -ForegroundColor Gray
} catch {
    Write-Host "❌ Metrics Endpoint Failed: $_" -ForegroundColor Red
}
Write-Host ""

# Test Projects API (requires authentication)
Write-Host "3. Testing Projects API..." -ForegroundColor Yellow
try {
    $projects = Invoke-RestMethod -Uri "$baseUrl/api/projects.php?user_id=1" -Method Get -ErrorAction SilentlyContinue
    Write-Host "✅ Projects API: Accessible" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Projects API: Requires authentication or no projects found" -ForegroundColor Yellow
}
Write-Host ""

Write-Host "✅ Testing Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📊 View in browser:" -ForegroundColor Cyan
Write-Host "   Health: $baseUrl/api/health.php" -ForegroundColor Gray
Write-Host "   Metrics: $baseUrl/api/metrics.php" -ForegroundColor Gray

