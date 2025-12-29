<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    public function index(): Response
    {
        $workspaces = Auth::user()
            ->workspaces()
            ->withCount(['spaces', 'members'])
            ->get();

        return Inertia::render('Workspaces/Index', [
            'workspaces' => $workspaces,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Workspaces/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
        ]);

        $workspace = Workspace::create([
            ...$validated,
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            'owner_id' => Auth::id(),
        ]);

        // Add owner as a member
        $workspace->members()->attach(Auth::id(), [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace created successfully.');
    }

    public function show(Workspace $workspace): Response
    {
        $this->authorize('view', $workspace);

        $workspace->load([
            'spaces' => fn($q) => $q->withCount('taskLists'),
            'members',
        ]);

        return Inertia::render('Workspaces/Show', [
            'workspace' => $workspace,
        ]);
    }

    public function edit(Workspace $workspace): Response
    {
        $this->authorize('update', $workspace);

        return Inertia::render('Workspaces/Edit', [
            'workspace' => $workspace,
        ]);
    }

    public function update(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
        ]);

        $workspace->update($validated);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace updated successfully.');
    }

    public function destroy(Workspace $workspace): RedirectResponse
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace deleted successfully.');
    }

    public function members(Workspace $workspace): Response
    {
        $this->authorize('view', $workspace);

        $workspace->load('members');

        return Inertia::render('Workspaces/Members', [
            'workspace' => $workspace,
        ]);
    }

    public function inviteMember(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin,member,guest',
        ]);

        $user = \App\Models\User::where('email', $validated['email'])->first();

        if ($workspace->members()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['email' => 'User is already a member of this workspace.']);
        }

        $workspace->members()->attach($user->id, [
            'role' => $validated['role'],
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Member invited successfully.');
    }

    public function removeMember(Workspace $workspace, \App\Models\User $user): RedirectResponse
    {
        $this->authorize('update', $workspace);

        if ($workspace->owner_id === $user->id) {
            return back()->withErrors(['error' => 'Cannot remove the workspace owner.']);
        }

        $workspace->members()->detach($user->id);

        return back()->with('success', 'Member removed successfully.');
    }
}
