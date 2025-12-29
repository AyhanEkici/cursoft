@echo off
echo ========================================
echo Pushing Cursoft to GitHub
echo ========================================
echo.

git remote set-url origin https://YOUR_TOKEN_HERE@github.com/AyhanEkici/cursoft.git

echo.
echo Pushing to GitHub...
echo.

git push -u origin main

echo.
echo ========================================
echo Push complete!
echo ========================================
echo.
echo Verify at: https://github.com/AyhanEkici/cursoft
echo.
pause

