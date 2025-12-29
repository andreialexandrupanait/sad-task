# SAD-Task

A modern, full-featured task management application inspired by ClickUp, built with Laravel 11, Vue 3, and Inertia.js.

## Features

### Core Features
- **Workspaces** - Multi-tenant organization for teams
- **Spaces** - Project containers within workspaces
- **Folders** - Group related lists together
- **Lists** - Task containers (similar to boards)
- **Tasks** - Comprehensive task management with:
  - Subtasks
  - Checklists
  - Comments & Mentions
  - File Attachments
  - Custom Fields
  - Time Tracking
  - Priority Levels
  - Status Workflow
  - Multiple Assignees
  - Due Dates & Reminders
  - Tags & Labels

### Views
- **List View** - Traditional list layout
- **Board View** - Kanban-style drag & drop
- **Calendar View** - Date-based visualization
- **Gantt View** - Timeline & dependencies

### Collaboration
- Real-time updates via WebSockets
- Activity feed & notifications
- Team member management
- Role-based permissions

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue 3 + Inertia.js
- **Styling**: Tailwind CSS
- **Database**: MySQL 8.0+ / PostgreSQL 15+
- **Real-time**: Laravel Reverb / Pusher
- **Queue**: Redis
- **Search**: Laravel Scout + Meilisearch

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+
- npm or pnpm
- MySQL 8.0+ or PostgreSQL 15+
- Redis (optional, for queues and caching)

## Installation

### 1. Clone the repository
```bash
git clone https://github.com/your-username/sad-task.git
cd sad-task
```

### 2. Install PHP dependencies
```bash
composer install
```

### 3. Install Node dependencies
```bash
npm install
```

### 4. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure database
Edit `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sad_task
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Run migrations and seeders
```bash
php artisan migrate --seed
```

### 7. Build assets
```bash
npm run dev
```

### 8. Start the development server
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Development

### Running tests
```bash
php artisan test
```

### Code formatting
```bash
./vendor/bin/pint
```

### Building for production
```bash
npm run build
```

## Project Structure

```
sad-task/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   ├── Policies/
│   └── Events/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   ├── Layouts/
│   │   ├── Pages/
│   │   ├── Composables/
│   │   └── Stores/
│   ├── css/
│   └── views/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php
└── tests/
```

## License

MIT License - see [LICENSE](LICENSE) for details.
