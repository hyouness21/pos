@echo off
title POS System - Update
color 0A
echo ============================================
echo        POS System - Update
echo ============================================
echo.

:: Check update.zip exists in this folder
if not exist "%~dp0update.zip" (
    color 0C
    echo ERROR: update.zip not found.
    echo Please put update.zip in the same folder as this file.
    echo.
    pause
    exit /b 1
)

echo [1/4] Installing update files...
powershell -Command "Expand-Archive -Path '%~dp0update.zip' -DestinationPath '%~dp0' -Force"
if %errorlevel% neq 0 (
    color 0C
    echo ERROR: Could not extract update.zip
    pause
    exit /b 1
)
echo Done.
echo.

echo [2/4] Updating database...
php artisan migrate --force
echo Done.
echo.

echo [3/4] Clearing cache...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo Done.
echo.

echo [4/4] Cleaning up...
del "%~dp0update.zip"
echo Done.
echo.

color 0A
echo ============================================
echo   Update complete! You can close this window.
echo ============================================
pause
