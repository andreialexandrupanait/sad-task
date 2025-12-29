# SAD-Task Setup Guide

## Step 1: Create Laravel Project

Open your terminal in the parent directory of where you want the project and run:

```bash
# Navigate to your projects folder
cd d:\Aplicatii GIT

# Create new Laravel project (this will create a fresh sad-task folder)
# First, backup or rename the existing sad-task folder if needed
composer create-project laravel/laravel sad-task-new

# Or if you want to install in the existing folder:
cd sad-task
composer create-project laravel/laravel . --prefer-dist
```

**Alternative using Laravel Installer:**
```bash
composer global require laravel/installer
laravel new sad-task
```

## Step 2: Install Required Packages

```bash
cd sad-task

# Install Inertia.js server-side
composer require inertiajs/inertia-laravel

# Install Ziggy for route handling in Vue
composer require tightenco/ziggy

# Install Laravel Breeze with Vue + Inertia (includes auth scaffolding)
composer require laravel/breeze --dev
php artisan breeze:install vue --inertia

# Install additional packages
composer require spatie/laravel-permission          # Role & permissions
composer require spatie/laravel-activitylog        # Activity logging
composer require spatie/laravel-medialibrary       # File attachments
composer require laravel/scout                      # Full-text search
composer require meilisearch/meilisearch-php       # Meilisearch driver

# Install Laravel Reverb for real-time (Laravel 11)
php artisan install:broadcasting
```

## Step 3: Install Frontend Dependencies

```bash
# The breeze:install command already runs npm install, but if needed:
npm install

# Additional Vue packages
npm install @vueuse/core                    # Vue composition utilities
npm install pinia                           # State management
npm install @headlessui/vue                 # Accessible UI components
npm install @heroicons/vue                  # Icons
npm install vuedraggable@next               # Drag and drop
npm install dayjs                           # Date handling
npm install @tanstack/vue-query             # Data fetching
npm install vue-toastification              # Toast notifications
npm install floating-vue                    # Tooltips and popovers
npm install @tiptap/vue-3 @tiptap/starter-kit @tiptap/extension-mention  # Rich text editor
```

## Step 4: Configure Environment

Copy the example environment file:
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your settings:
```env
APP_NAME="SAD Task"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sad_task
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=reverb
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database

REVERB_APP_ID=sad-task
REVERB_APP_KEY=sad-task-key
REVERB_APP_SECRET=sad-task-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## Step 5: Create Database

```bash
# Using MySQL CLI
mysql -u root -p
CREATE DATABASE sad_task CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Or using PostgreSQL
psql -U postgres
CREATE DATABASE sad_task;
\q
```

## Step 6: Run Migrations

```bash
php artisan migrate
```

## Step 7: Publish Configurations

```bash
# Publish Spatie Permission config
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Publish Activity Log config
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"

# Publish Media Library config
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"
```

## Step 8: Run the Application

Terminal 1 - Laravel server:
```bash
php artisan serve
```

Terminal 2 - Vite dev server:
```bash
npm run dev
```

Terminal 3 - Queue worker (optional):
```bash
php artisan queue:work
```

Terminal 4 - Reverb WebSocket server (optional):
```bash
php artisan reverb:start
```

## Step 9: Access the Application

Open your browser and navigate to:
```
http://localhost:8000
```

## Troubleshooting

### Common Issues

1. **npm install fails**: Make sure you have Node.js 18+ installed
2. **Database connection error**: Verify your `.env` database credentials
3. **Permission denied**: Run `chmod -R 775 storage bootstrap/cache` on Linux/Mac
4. **Vite not loading**: Make sure `npm run dev` is running in a separate terminal

### Windows-Specific

If you're on Windows and encounter issues:
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Regenerate autoload
composer dump-autoload
```
