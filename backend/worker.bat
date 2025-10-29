@echo off
:loop
php artisan queue:listen
echo [%date% %time%] Worker restart in 3 seconds later .....
timeout /t 3
goto loop
@REM start cmd /k worker.bat