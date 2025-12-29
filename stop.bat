@echo off
echo.
echo Stopping SAD Task containers...
docker-compose -f docker-compose.dev.yml down
echo.
echo All containers stopped.
pause
