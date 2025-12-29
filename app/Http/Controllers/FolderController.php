<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Space;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function store(Request $request, Workspace $workspace, Space $space): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $space->folders()->create([
            ...$validated,
            'position' => $space->folders()->max('position') + 1,
        ]);

        return back()->with('success', 'Folder created successfully.');
    }

    public function update(Request $request, Workspace $workspace, Space $space, Folder $folder): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_hidden' => 'boolean',
        ]);

        $folder->update($validated);

        return back()->with('success', 'Folder updated successfully.');
    }

    public function destroy(Workspace $workspace, Space $space, Folder $folder): RedirectResponse
    {
        $this->authorize('update', $workspace);

        // Move lists to space root before deleting folder
        $folder->taskLists()->update(['folder_id' => null]);
        $folder->delete();

        return back()->with('success', 'Folder deleted successfully.');
    }

    public function reorder(Request $request, Workspace $workspace, Space $space): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'folders' => 'required|array',
            'folders.*.id' => 'required|exists:folders,id',
            'folders.*.position' => 'required|integer|min:0',
        ]);

        foreach ($validated['folders'] as $folderData) {
            Folder::where('id', $folderData['id'])
                ->where('space_id', $space->id)
                ->update(['position' => $folderData['position']]);
        }

        return back()->with('success', 'Folders reordered successfully.');
    }
}
