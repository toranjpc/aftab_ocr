@echo off
:loop
php D:\khanzadi\ocr_codes\backend\artisan schedule:run
timeout /t 300 >nul
goto loop
