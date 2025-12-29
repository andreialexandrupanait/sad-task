<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\Status;
use App\Models\TaskList;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function store(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'type' => 'nullable|in:open,in_progress,done,closed,custom',
            'is_closed' => 'boolean',
        ]);

        $list->statuses()->create([
            ...$validated,
            'position' => $list->statuses()->max('position') + 1,
        ]);

        return back()->with('success', 'Status created successfully.');
    }

    public function update(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Status $status
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'type' => 'nullable|in:open,in_progress,done,closed,custom',
            'is_closed' => 'boolean',
            'is_default' => 'boolean',
        ]);

        // If setting as default, unset other defaults
        if (!empty($validated['is_default']) && $validated['is_default']) {
            $list->statuses()->where('id', '!=', $status->id)->update(['is_default' => false]);
        }

        $status->update($validated);

        return back()->with('success', 'Status updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Status $status
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        // Don't delete if it's the only status
        if ($list->statuses()->count() <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the only status.']);
        }

        // Move tasks to default status before deleting
        $defaultStatus = $list->statuses()
            ->where('id', '!=', $status->id)
            ->where('is_default', true)
            ->first() ?? $list->statuses()->where('id', '!=', $status->id)->first();

        $status->tasks()->update(['status_id' => $defaultStatus->id]);
        $status->delete();

        return back()->with('success', 'Status deleted successfully.');
    }

    public function reorder(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list
    ): RedirectResponse {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'statuses' => 'required|array',
            'statuses.*.id' => 'required|exists:statuses,id',
            'statuses.*.position' => 'required|integer|min:0',
        ]);

        foreach ($validated['statuses'] as $statusData) {
            Status::where('id', $statusData['id'])
                ->where('task_list_id', $list->id)
                ->update(['position' => $statusData['position']]);
        }

        return back()->with('success', 'Statuses reordered successfully.');
    }
}
