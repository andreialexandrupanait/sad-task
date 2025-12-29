<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SpaceController extends Controller
{
    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_private' => 'boolean',
        ]);

        $space = $workspace->spaces()->create([
            ...$validated,
            'slug' => Str::slug($validated['name']),
            'position' => $workspace->spaces()->max('position') + 1,
        ]);

        return redirect()->route('spaces.show', [$workspace, $space])
            ->with('success', 'Space created successfully.');
    }

    public function show(Workspace $workspace, Space $space): Response
    {
        $this->authorize('view', $workspace);

        $space->load([
            'folders.taskLists',
            'folderlessLists',
        ]);

        return Inertia::render('Spaces/Show', [
            'workspace' => $workspace,
            'space' => $space,
        ]);
    }

    public function update(Request $request, Workspace $workspace, Space $space): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_private' => 'boolean',
        ]);

        $space->update($validated);

        return back()->with('success', 'Space updated successfully.');
    }

    public function destroy(Workspace $workspace, Space $space): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $space->delete();

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Space deleted successfully.');
    }

    public function reorder(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'spaces' => 'required|array',
            'spaces.*.id' => 'required|exists:spaces,id',
            'spaces.*.position' => 'required|integer|min:0',
        ]);

        foreach ($validated['spaces'] as $spaceData) {
            Space::where('id', $spaceData['id'])
                ->where('workspace_id', $workspace->id)
                ->update(['position' => $spaceData['position']]);
        }

        return back()->with('success', 'Spaces reordered successfully.');
    }
}
