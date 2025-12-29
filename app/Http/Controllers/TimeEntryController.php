<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\TimeEntry;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeEntryController extends Controller
{
    public function store(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task
    ): RedirectResponse {
        $this->authorize('view', $workspace);

        $validated = $request->validate([
            'description' => 'nullable|string|max:500',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after:started_at',
            'duration' => 'nullable|integer|min:1',
            'is_billable' => 'boolean',
        ]);

        // Calculate duration if not provided
        if (empty($validated['duration']) && !empty($validated['ended_at'])) {
            $start = \Carbon\Carbon::parse($validated['started_at']);
            $end = \Carbon\Carbon::parse($validated['ended_at']);
            $validated['duration'] = $start->diffInMinutes($end);
        }

        $timeEntry = $task->timeEntries()->create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        // Update task's time spent
        if ($timeEntry->duration > 0) {
            $task->increment('time_spent', $timeEntry->duration);
        }

        return back()->with('success', 'Time entry added successfully.');
    }

    public function startTimer(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task
    ): RedirectResponse {
        $this->authorize('view', $workspace);

        // Stop any running timer for this user
        TimeEntry::where('user_id', Auth::id())
            ->where('is_running', true)
            ->each(function ($entry) {
                $entry->stop();
            });

        // Start new timer
        $task->timeEntries()->create([
            'user_id' => Auth::id(),
            'started_at' => now(),
            'is_running' => true,
        ]);

        return back()->with('success', 'Timer started.');
    }

    public function stopTimer(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        TimeEntry $timeEntry
    ): RedirectResponse {
        $this->authorize('view', $workspace);

        if ($timeEntry->user_id !== Auth::id()) {
            abort(403);
        }

        $timeEntry->stop();

        return back()->with('success', 'Timer stopped.');
    }

    public function update(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        TimeEntry $timeEntry
    ): RedirectResponse {
        $this->authorize('view', $workspace);

        if ($timeEntry->user_id !== Auth::id() && !$workspace->isAdmin(Auth::user())) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => 'nullable|string|max:500',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after:started_at',
            'duration' => 'nullable|integer|min:1',
            'is_billable' => 'boolean',
        ]);

        $oldDuration = $timeEntry->duration;

        // Recalculate duration if times changed
        if (!empty($validated['ended_at'])) {
            $start = \Carbon\Carbon::parse($validated['started_at']);
            $end = \Carbon\Carbon::parse($validated['ended_at']);
            $validated['duration'] = $start->diffInMinutes($end);
        }

        $timeEntry->update($validated);

        // Update task's time spent
        $durationDiff = ($timeEntry->duration ?? 0) - $oldDuration;
        if ($durationDiff !== 0) {
            $task->increment('time_spent', $durationDiff);
        }

        return back()->with('success', 'Time entry updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        TimeEntry $timeEntry
    ): RedirectResponse {
        $this->authorize('view', $workspace);

        if ($timeEntry->user_id !== Auth::id() && !$workspace->isAdmin(Auth::user())) {
            abort(403);
        }

        // Update task's time spent
        $task->decrement('time_spent', $timeEntry->duration);

        $timeEntry->delete();

        return back()->with('success', 'Time entry deleted successfully.');
    }
}
