<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Mention;
use App\Models\Space;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
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
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
            'mentions' => 'nullable|array',
            'mentions.*' => 'exists:users,id',
        ]);

        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // Handle mentions
        if (!empty($validated['mentions'])) {
            foreach ($validated['mentions'] as $userId) {
                Mention::create([
                    'comment_id' => $comment->id,
                    'user_id' => $userId,
                ]);
            }
        }

        Activity::log(Activity::TYPE_COMMENT_ADDED, $task, Auth::user(), [
            'comment_id' => $comment->id,
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    public function update(
        Request $request,
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Comment $comment
    ): RedirectResponse {
        $this->authorize('view', $workspace);

        // Only comment author can edit
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update($validated);

        return back()->with('success', 'Comment updated successfully.');
    }

    public function destroy(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Comment $comment
    ): RedirectResponse {
        $this->authorize('view', $workspace);

        // Only comment author or workspace admin can delete
        if ($comment->user_id !== Auth::id() && !$workspace->isAdmin(Auth::user())) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }

    public function resolve(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Comment $comment
    ): RedirectResponse {
        $this->authorize('view', $workspace);

        $comment->resolve(Auth::user());

        return back()->with('success', 'Comment resolved.');
    }

    public function unresolve(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Comment $comment
    ): RedirectResponse {
        $this->authorize('view', $workspace);

        $comment->unresolve();

        return back()->with('success', 'Comment unresolved.');
    }
}
