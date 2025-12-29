<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Space;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo user
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@sadtask.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create a workspace
        $workspace = Workspace::create([
            'name' => 'My Workspace',
            'slug' => 'my-workspace',
            'description' => 'Welcome to SAD Task! This is your first workspace.',
            'color' => '#6366f1',
            'owner_id' => $user->id,
        ]);

        // Add user as workspace member
        $workspace->members()->attach($user->id, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        // Create spaces
        $productSpace = Space::create([
            'workspace_id' => $workspace->id,
            'name' => 'Product Development',
            'slug' => 'product-development',
            'description' => 'All product-related projects and tasks',
            'color' => '#8b5cf6',
            'position' => 0,
        ]);

        $marketingSpace = Space::create([
            'workspace_id' => $workspace->id,
            'name' => 'Marketing',
            'slug' => 'marketing',
            'description' => 'Marketing campaigns and content',
            'color' => '#ec4899',
            'position' => 1,
        ]);

        // Create folders in Product space
        $backendFolder = Folder::create([
            'space_id' => $productSpace->id,
            'name' => 'Backend',
            'position' => 0,
        ]);

        $frontendFolder = Folder::create([
            'space_id' => $productSpace->id,
            'name' => 'Frontend',
            'position' => 1,
        ]);

        // Create lists
        $backlogList = TaskList::create([
            'space_id' => $productSpace->id,
            'folder_id' => $backendFolder->id,
            'name' => 'API Development',
            'slug' => 'api-development',
            'description' => 'Backend API tasks',
            'position' => 0,
        ]);

        $uiList = TaskList::create([
            'space_id' => $productSpace->id,
            'folder_id' => $frontendFolder->id,
            'name' => 'UI Components',
            'slug' => 'ui-components',
            'description' => 'Frontend component library',
            'position' => 0,
        ]);

        $campaignList = TaskList::create([
            'space_id' => $marketingSpace->id,
            'name' => 'Q1 Campaign',
            'slug' => 'q1-campaign',
            'description' => 'First quarter marketing campaign',
            'position' => 0,
        ]);

        // Create sample tasks
        $this->createSampleTasks($backlogList, $user);
        $this->createSampleTasks($uiList, $user);
        $this->createSampleTasks($campaignList, $user);
    }

    private function createSampleTasks(TaskList $list, User $user): void
    {
        $defaultStatus = $list->getDefaultStatus();
        $inProgressStatus = $list->statuses()->where('type', 'in_progress')->first();
        $doneStatus = $list->statuses()->where('type', 'done')->first();

        $tasks = [
            [
                'title' => 'Set up project structure',
                'description' => 'Initialize the project with proper folder structure and configuration files.',
                'status_id' => $doneStatus->id,
                'priority' => 2,
                'completed_at' => now()->subDays(3),
            ],
            [
                'title' => 'Implement authentication',
                'description' => 'Add user login, registration, and password reset functionality.',
                'status_id' => $inProgressStatus->id,
                'priority' => 1,
                'due_date' => now()->addDays(2),
            ],
            [
                'title' => 'Create database migrations',
                'description' => 'Design and create all necessary database tables and relationships.',
                'status_id' => $inProgressStatus->id,
                'priority' => 2,
                'due_date' => now()->addDays(1),
            ],
            [
                'title' => 'Write API documentation',
                'description' => 'Document all API endpoints with examples and response formats.',
                'status_id' => $defaultStatus->id,
                'priority' => 3,
                'due_date' => now()->addDays(7),
            ],
            [
                'title' => 'Add unit tests',
                'description' => 'Write comprehensive unit tests for all major components.',
                'status_id' => $defaultStatus->id,
                'priority' => 3,
                'due_date' => now()->addDays(14),
            ],
            [
                'title' => 'Performance optimization',
                'description' => 'Analyze and optimize slow queries and API responses.',
                'status_id' => $defaultStatus->id,
                'priority' => 4,
            ],
        ];

        foreach ($tasks as $index => $taskData) {
            $task = Task::create([
                'task_list_id' => $list->id,
                'status_id' => $taskData['status_id'],
                'created_by' => $user->id,
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'priority' => $taskData['priority'],
                'due_date' => $taskData['due_date'] ?? null,
                'completed_at' => $taskData['completed_at'] ?? null,
                'position' => $index,
            ]);

            // Assign user to some tasks
            if ($index < 3) {
                $task->assignees()->attach($user->id);
            }
        }
    }
}
