<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Space;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function store(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $task->checklists()->create([
            ...$validated,
            'position' => $task->checklists()->max('position') + 1,
        ]);

        return back()->with('success', 'Checklist created successfully.');
    }

    public function update(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Checklist $checklist
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $checklist->update($validated);

        return back()->with('success', 'Checklist updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Checklist $checklist
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $checklist->delete();

        return back()->with('success', 'Checklist deleted successfully.');
    }

    public function addItem(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Checklist $checklist
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'content' => 'required|string|max:500',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $checklist->items()->create([
            ...$validated,
            'position' => $checklist->items()->max('position') + 1,
        ]);

        return back()->with('success', 'Item added successfully.');
    }

    public function updateItem(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Checklist $checklist,
        ChecklistItem $item
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'content' => 'sometimes|required|string|max:500',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'is_completed' => 'boolean',
        ]);

        if (isset($validated['is_completed'])) {
            if ($validated['is_completed']) {
                $item->markComplete();
            } else {
                $item->markIncomplete();
            }
            unset($validated['is_completed']);
        }

        if (!empty($validated)) {
            $item->update($validated);
        }

        return back()->with('success', 'Item updated successfully.');
    }

    public function deleteItem(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Checklist $checklist,
        ChecklistItem $item
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $item->delete();

        return back()->with('success', 'Item deleted successfully.');
    }

    public function reorderItems(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Checklist $checklist
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:checklist_items,id',
            'items.*.position' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $itemData) {
            ChecklistItem::where('id', $itemData['id'])
                ->where('checklist_id', $checklist->id)
                ->update(['position' => $itemData['position']]);
        }

        return back()->with('success', 'Items reordered successfully.');
    }
}
