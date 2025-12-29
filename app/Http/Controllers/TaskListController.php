<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\TaskList;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TaskListController extends Controller
{
    public function store(Request $request, Workspace $workspace, Space $space): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'folder_id' => 'nullable|exists:folders,id',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'default_view' => 'nullable|in:list,board,calendar,gantt',
        ]);

        $taskList = $space->taskLists()->create([
            ...$validated,
            'slug' => Str::slug($validated['name']),
            'position' => $space->taskLists()->max('position') + 1,
        ]);

        return redirect()->route('lists.show', [$workspace, $space, $taskList])
            ->with('success', 'List created successfully.');
    }

    public function show(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list
    ): Response {
        $this->authorize('view', $workspace);

        $view = $request->get('view', $list->default_view);

        $list->load([
            'statuses',
            'customFields',
        ]);

        $tasksQuery = $list->tasks()
            ->with([
                'status',
                'assignees',
                'creator',
                'subtasks.status',
                'subtasks.assignees',
                'tags.tag',
                'checklists.items',
                'comments.user',
                'attachments',
                'activities.user',
            ])
            ->whereNull('parent_id');

        // Apply filters
        if ($request->has('status')) {
            $tasksQuery->where('status_id', $request->get('status'));
        }

        if ($request->has('assignee')) {
            $tasksQuery->whereHas('assignees', function ($q) use ($request) {
                $q->where('users.id', $request->get('assignee'));
            });
        }

        if ($request->has('priority')) {
            $tasksQuery->where('priority', $request->get('priority'));
        }

        // Apply sorting
        $sort = $request->get('sort', 'position');
        $direction = $request->get('direction', 'asc');

        if ($sort === 'due_date') {
            $tasksQuery->orderByRaw('due_date IS NULL, due_date ' . $direction);
        } else {
            $tasksQuery->orderBy($sort, $direction);
        }

        $tasks = $tasksQuery->get();

        return Inertia::render('Lists/Show', [
            'workspace' => $workspace,
            'space' => $space,
            'list' => $list,
            'tasks' => $tasks,
            'view' => $view,
            'filters' => $request->only(['status', 'assignee', 'priority']),
        ]);
    }

    public function update(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'folder_id' => 'nullable|exists:folders,id',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'default_view' => 'nullable|in:list,board,calendar,gantt',
        ]);

        $list->update($validated);

        return back()->with('success', 'List updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        Space $space,
        TaskList $list
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $list->delete();

        return redirect()->route('spaces.show', [$workspace, $space])
            ->with('success', 'List deleted successfully.');
    }

    public function duplicate(
        Workspace $workspace,
        Space $space,
        TaskList $list
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $newList = $list->replicate();
        $newList->name = $list->name . ' (Copy)';
        $newList->slug = Str::slug($newList->name);
        $newList->position = $space->taskLists()->max('position') + 1;
        $newList->save();

        // Duplicate statuses
        foreach ($list->statuses as $status) {
            $newStatus = $status->replicate();
            $newStatus->task_list_id = $newList->id;
            $newStatus->save();
        }

        return redirect()->route('lists.show', [$workspace, $space, $newList])
            ->with('success', 'List duplicated successfully.');
    }
}
