<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Task;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's workspaces with spaces and task lists
        $workspaces = $user->workspaces()
            ->with([
                'spaces' => function ($query) {
                    $query->orderBy('position');
                },
                'spaces.folders' => function ($query) {
                    $query->orderBy('position');
                },
                'spaces.folders.taskLists' => function ($query) {
                    $query->orderBy('position');
                },
                'spaces.folderlessLists' => function ($query) {
                    $query->orderBy('position');
                },
            ])
            ->get();

        // Also get owned workspaces
        $ownedWorkspaces = $user->ownedWorkspaces()
            ->whereNotIn('id', $workspaces->pluck('id'))
            ->with([
                'spaces' => function ($query) {
                    $query->orderBy('position');
                },
                'spaces.folders' => function ($query) {
                    $query->orderBy('position');
                },
                'spaces.folders.taskLists' => function ($query) {
                    $query->orderBy('position');
                },
                'spaces.folderlessLists' => function ($query) {
                    $query->orderBy('position');
                },
            ])
            ->get();

        $allWorkspaces = $workspaces->merge($ownedWorkspaces);

        // Get today's tasks (due today or overdue, assigned to user)
        $today = Carbon::today();
        $todayTasks = Task::whereHas('assignees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where(function ($query) use ($today) {
                $query->whereDate('due_date', '<=', $today)
                    ->orWhereNull('due_date');
            })
            ->whereNull('completed_at')
            ->with(['status', 'taskList'])
            ->orderBy('priority')
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->title,
                    'status' => $task->status?->slug ?? 'todo',
                    'priority' => $this->getPriorityLabel($task->priority),
                    'list' => $task->taskList?->name ?? 'Unknown',
                    'due_date' => $task->due_date?->format('Y-m-d'),
                    'is_overdue' => $task->due_date && $task->due_date->isPast(),
                ];
            });

        // Get weekly stats
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $workspaceIds = $allWorkspaces->pluck('id');

        // Completed this week
        $completedCount = Task::whereHas('taskList.space', function ($query) use ($workspaceIds) {
                $query->whereIn('workspace_id', $workspaceIds);
            })
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
            ->count();

        // In progress (assigned to user, not completed)
        $inProgressCount = Task::whereHas('assignees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereNull('completed_at')
            ->whereHas('status', function ($query) {
                $query->where('type', 'in_progress');
            })
            ->count();

        // Overdue tasks
        $overdueCount = Task::whereHas('assignees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereNull('completed_at')
            ->whereDate('due_date', '<', $today)
            ->count();

        // Recent activity
        $recentActivity = Activity::whereIn('workspace_id', $workspaceIds)
            ->with(['user', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'description' => $this->getActivityDescription($activity),
                    'user' => $activity->user?->name ?? 'System',
                    'created_at' => $activity->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Dashboard', [
            'workspaces' => $allWorkspaces,
            'todayTasks' => $todayTasks,
            'stats' => [
                'completed' => $completedCount,
                'inProgress' => $inProgressCount,
                'overdue' => $overdueCount,
            ],
            'recentActivity' => $recentActivity,
        ]);
    }

    private function getPriorityLabel(int $priority): string
    {
        return match ($priority) {
            1 => 'urgent',
            2 => 'high',
            3 => 'medium',
            4 => 'low',
            default => 'medium',
        };
    }

    private function getActivityDescription(Activity $activity): string
    {
        $subjectName = '';

        if ($activity->subject instanceof Task) {
            $subjectName = '"' . $activity->subject->title . '"';
        } elseif ($activity->subject && method_exists($activity->subject, 'name')) {
            $subjectName = '"' . $activity->subject->name . '"';
        }

        return match ($activity->type) {
            Activity::TYPE_CREATED => "Created {$subjectName}",
            Activity::TYPE_UPDATED => "Updated {$subjectName}",
            Activity::TYPE_COMPLETED => "Completed {$subjectName}",
            Activity::TYPE_STATUS_CHANGED => "Changed status of {$subjectName}",
            Activity::TYPE_ASSIGNED => "Assigned {$subjectName}",
            Activity::TYPE_COMMENT_ADDED => "Commented on {$subjectName}",
            default => ucfirst(str_replace('_', ' ', $activity->type)),
        };
    }
}
