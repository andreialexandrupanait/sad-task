@echo off
echo.
echo ========================================
echo    SAD Task - Docker Setup
echo ========================================
echo.

REM Check if Docker is running
docker info >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Docker is not running!
    echo Please start Docker Desktop and try again.
    pause
    exit /b 1
)

REM Copy environment file if it doesn't exist
if not exist ".env" (
    echo [INFO] Creating .env file from .env.docker...
    copy .env.docker .env
)

echo [INFO] Building and starting containers...
echo This may take a few minutes on first run...
echo.

docker-compose -f docker-compose.dev.yml up -d --build

echo.
echo [INFO] Waiting for containers to be ready...
timeout /t 10 /nobreak >nul

echo [INFO] Installing Composer dependencies...
docker-compose -f docker-compose.dev.yml exec -T app composer install --no-interaction

echo [INFO] Generating application key...
docker-compose -f docker-compose.dev.yml exec -T app php artisan key:generate --force

echo [INFO] Running database migrations...
docker-compose -f docker-compose.dev.yml exec -T app php artisan migrate --force

echo [INFO] Seeding database with demo data...
docker-compose -f docker-compose.dev.yml exec -T app php artisan db:seed --force

echo.
echo ========================================
echo    SAD Task is now running!
echo ========================================
echo.
echo    App:        http://localhost:8000
echo    phpMyAdmin: http://localhost:8080
echo    Vite:       http://localhost:5173
echo.
echo    Demo Login:
echo    Email:    demo@sadtask.com
echo    Password: password
echo.
echo    Commands:
echo    - Stop:    docker-compose -f docker-compose.dev.yml down
echo    - Logs:    docker-compose -f docker-compose.dev.yml logs -f
echo    - Shell:   docker-compose -f docker-compose.dev.yml exec app bash
echo.
pause
