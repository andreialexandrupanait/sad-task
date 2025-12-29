<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Space;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function store(Request $request, Workspace $workspace, Space $space, TaskList $list): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'nullable|exists:statuses,id',
            'priority' => 'nullable|integer|between:1,4',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'parent_id' => 'nullable|exists:tasks,id',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
            'time_estimate' => 'nullable|integer|min:0',
        ]);

        $task = $list->tasks()->create([
            ...$validated,
            'created_by' => Auth::id(),
            'position' => $list->tasks()->whereNull('parent_id')->max('position') + 1,
        ]);

        // Attach assignees
        if (!empty($validated['assignee_ids'])) {
            $task->assignees()->attach($validated['assignee_ids']);
        }

        Activity::log(Activity::TYPE_CREATED, $task, Auth::user());

        if ($request->wantsJson()) {
            return response()->json(['task' => $task->load(['status', 'assignees', 'creator'])]);
        }

        return back()->with('success', 'Task created successfully.');
    }

    public function show(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task
    ): Response {
        $this->authorize('view', $workspace);

        $task->load([
            'status',
            'creator',
            'assignees',
            'watchers',
            'subtasks.status',
            'subtasks.assignees',
            'checklists.items.assignee',
            'comments.user',
            'comments.replies.user',
            'attachments.uploader',
            'timeEntries.user',
            'customFieldValues.customField',
            'tags.tag',
            'dependencies',
            'activities.user',
        ]);

        return Inertia::render('Tasks/Show', [
            'workspace' => $workspace,
            'space' => $space,
            'list' => $list->load('statuses'),
            'task' => $task,
        ]);
    }

    public function update(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'nullable|exists:statuses,id',
            'priority' => 'nullable|integer|between:1,4',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'time_estimate' => 'nullable|integer|min:0',
            'is_archived' => 'boolean',
        ]);

        $oldStatus = $task->status_id;
        $oldPriority = $task->priority;

        $task->update($validated);

        // Log activity for specific changes
        if (isset($validated['status_id']) && $oldStatus !== $validated['status_id']) {
            Activity::log(Activity::TYPE_STATUS_CHANGED, $task, Auth::user(), [
                'old' => $oldStatus,
                'new' => $validated['status_id'],
            ]);

            // Mark as completed if status is closed
            $newStatus = $task->fresh()->status;
            if ($newStatus && $newStatus->is_closed && !$task->completed_at) {
                $task->update(['completed_at' => now()]);
                Activity::log(Activity::TYPE_COMPLETED, $task, Auth::user());
            } elseif ($newStatus && !$newStatus->is_closed && $task->completed_at) {
                $task->update(['completed_at' => null]);
                Activity::log(Activity::TYPE_REOPENED, $task, Auth::user());
            }
        }

        if (isset($validated['priority']) && $oldPriority !== $validated['priority']) {
            Activity::log(Activity::TYPE_PRIORITY_CHANGED, $task, Auth::user(), [
                'old' => $oldPriority,
                'new' => $validated['priority'],
            ]);
        }

        return back()->with('success', 'Task updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        Activity::log(Activity::TYPE_DELETED, $task, Auth::user());

        $task->delete();

        return redirect()->route('lists.show', [$workspace, $space, $list])
            ->with('success', 'Task deleted successfully.');
    }

    public function updateAssignees(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'assignee_ids' => 'required|array',
            'assignee_ids.*' => 'exists:users,id',
        ]);

        $oldAssignees = $task->assignees->pluck('id')->toArray();
        $task->assignees()->sync($validated['assignee_ids']);

        $added = array_diff($validated['assignee_ids'], $oldAssignees);
        $removed = array_diff($oldAssignees, $validated['assignee_ids']);

        foreach ($added as $userId) {
            Activity::log(Activity::TYPE_ASSIGNED, $task, Auth::user(), ['user_id' => $userId]);
        }

        foreach ($removed as $userId) {
            Activity::log(Activity::TYPE_UNASSIGNED, $task, Auth::user(), ['user_id' => $userId]);
        }

        return back()->with('success', 'Assignees updated successfully.');
    }

    public function reorder(Request $request, Workspace $workspace, Space $space, TaskList $list): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.position' => 'required|integer|min:0',
            'tasks.*.status_id' => 'nullable|exists:statuses,id',
        ]);

        foreach ($validated['tasks'] as $taskData) {
            $task = Task::find($taskData['id']);

            if ($task->task_list_id !== $list->id) {
                continue;
            }

            $updateData = ['position' => $taskData['position']];

            if (isset($taskData['status_id']) && $task->status_id !== $taskData['status_id']) {
                $updateData['status_id'] = $taskData['status_id'];
                Activity::log(Activity::TYPE_STATUS_CHANGED, $task, Auth::user(), [
                    'old' => $task->status_id,
                    'new' => $taskData['status_id'],
                ]);
            }

            $task->update($updateData);
        }

        return back()->with('success', 'Tasks reordered successfully.');
    }

    public function move(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'task_list_id' => 'required|exists:task_lists,id',
        ]);

        $newList = TaskList::find($validated['task_list_id']);

        // Verify the new list is in the same workspace
        if ($newList->space->workspace_id !== $workspace->id) {
            return back()->withErrors(['error' => 'Cannot move task to a different workspace.']);
        }

        $task->update([
            'task_list_id' => $newList->id,
            'status_id' => $newList->getDefaultStatus()?->id,
            'position' => $newList->tasks()->max('position') + 1,
        ]);

        return back()->with('success', 'Task moved successfully.');
    }
}
