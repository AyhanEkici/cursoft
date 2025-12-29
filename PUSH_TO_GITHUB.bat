@echo off
echo ========================================
echo Pushing to GitHub...
echo ========================================
echo.

cd /d E:\xampp\htdocs\cursoft

echo Updating remote with new token...
echo NOTE: Replace YOUR_TOKEN_HERE with your actual GitHub PAT token
git remote set-url origin https://YOUR_TOKEN_HERE@github.com/AyhanEkici/cursoft.git

echo.
echo Checking Git status...
git status

echo.
echo Pushing to GitHub (this may take a while)...
git push origin main

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo SUCCESS! Code pushed to GitHub!
    echo ========================================
) else (
    echo.
    echo ========================================
    echo ERROR: Push failed!
    echo ========================================
    echo.
    echo Troubleshooting:
    echo 1. Check your internet connection
    echo 2. Verify GitHub repository exists: https://github.com/AyhanEkici/cursoft
    echo 3. Check if PAT token is valid
    echo 4. Try: git push origin main --verbose
)

pause

