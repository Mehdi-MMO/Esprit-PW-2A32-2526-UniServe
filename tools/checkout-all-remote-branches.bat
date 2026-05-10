@echo off
setlocal
cd /d "%~dp0.."
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0checkout-all-remote-branches.ps1"
set EC=%ERRORLEVEL%
if %EC% neq 0 pause
exit /b %EC%
