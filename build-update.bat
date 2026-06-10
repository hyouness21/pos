@echo off
title Build Update Package
color 0B
echo ============================================
echo     Building update.zip for client
echo ============================================
echo.

echo [1/3] Building assets...
call npm run build
echo Done.
echo.

echo [2/3] Creating update.zip...
if exist "%~dp0update.zip" del "%~dp0update.zip"

powershell -Command ^
  "Compress-Archive -Path ^
    '%~dp0app', ^
    '%~dp0config', ^
    '%~dp0database', ^
    '%~dp0lang', ^
    '%~dp0resources', ^
    '%~dp0routes', ^
    '%~dp0public\build' ^
  -DestinationPath '%~dp0update.zip' -Force"

echo Done.
echo.

echo [3/3] Ready.
echo.
echo update.zip has been created.
echo Send this file to the client via WhatsApp / USB.
echo The client should:
echo   1. Put update.zip in the pos folder
echo   2. Double-click update.bat
echo.
pause
