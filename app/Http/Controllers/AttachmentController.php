<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Space;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
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
            'files' => 'required|array',
            'files.*' => 'file|max:102400', // 100MB max
        ]);

        foreach ($request->file('files') as $file) {
            $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs(
                "attachments/{$workspace->id}/{$task->id}",
                $name,
                'local'
            );

            $task->attachments()->create([
                'uploaded_by' => Auth::id(),
                'name' => $name,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        Activity::log(Activity::TYPE_ATTACHMENT_ADDED, $task, Auth::user());

        return back()->with('success', 'Files uploaded successfully.');
    }

    public function download(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Attachment $attachment
    ) {
        $this->authorize('view', $workspace);

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_name
        );
    }

    public function destroy(
        Workspace $workspace,
        Space $space,
        TaskList $list,
        Task $task,
        Attachment $attachment
    ): RedirectResponse {
        $this->authorize('view', $workspace);

        if ($attachment->uploaded_by !== Auth::id() && !$workspace->isAdmin(Auth::user())) {
            abort(403);
        }

        $attachment->delete();

        return back()->with('success', 'Attachment deleted successfully.');
    }
}
