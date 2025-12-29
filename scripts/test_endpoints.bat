@echo off
REM Batch Script to Test Cursoft API Endpoints
REM Usage: scripts\test_endpoints.bat

echo.
echo Testing Cursoft API Endpoints
echo ================================
echo.

echo 1. Testing Health Endpoint...
curl -s http://localhost/cursoft/api/health.php
echo.
echo.

echo 2. Testing Metrics Endpoint...
curl -s http://localhost/cursoft/api/metrics.php
echo.
echo.

echo Testing Complete!
echo.
echo View in browser:
echo   Health: http://localhost/cursoft/api/health.php
echo   Metrics: http://localhost/cursoft/api/metrics.php
echo.

pause

