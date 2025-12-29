<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskListController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Workspaces
    Route::resource('workspaces', WorkspaceController::class);
    Route::get('/workspaces/{workspace}/members', [WorkspaceController::class, 'members'])
        ->name('workspaces.members');
    Route::post('/workspaces/{workspace}/members', [WorkspaceController::class, 'inviteMember'])
        ->name('workspaces.members.invite');
    Route::delete('/workspaces/{workspace}/members/{user}', [WorkspaceController::class, 'removeMember'])
        ->name('workspaces.members.remove');

    // Spaces
    Route::post('/workspaces/{workspace}/spaces', [SpaceController::class, 'store'])
        ->name('spaces.store');
    Route::get('/workspaces/{workspace}/spaces/{space}', [SpaceController::class, 'show'])
        ->name('spaces.show');
    Route::put('/workspaces/{workspace}/spaces/{space}', [SpaceController::class, 'update'])
        ->name('spaces.update');
    Route::delete('/workspaces/{workspace}/spaces/{space}', [SpaceController::class, 'destroy'])
        ->name('spaces.destroy');
    Route::post('/workspaces/{workspace}/spaces/reorder', [SpaceController::class, 'reorder'])
        ->name('spaces.reorder');

    // Folders
    Route::post('/workspaces/{workspace}/spaces/{space}/folders', [FolderController::class, 'store'])
        ->name('folders.store');
    Route::put('/workspaces/{workspace}/spaces/{space}/folders/{folder}', [FolderController::class, 'update'])
        ->name('folders.update');
    Route::delete('/workspaces/{workspace}/spaces/{space}/folders/{folder}', [FolderController::class, 'destroy'])
        ->name('folders.destroy');
    Route::post('/workspaces/{workspace}/spaces/{space}/folders/reorder', [FolderController::class, 'reorder'])
        ->name('folders.reorder');

    // Task Lists
    Route::post('/workspaces/{workspace}/spaces/{space}/lists', [TaskListController::class, 'store'])
        ->name('lists.store');
    Route::get('/workspaces/{workspace}/spaces/{space}/lists/{list}', [TaskListController::class, 'show'])
        ->name('lists.show');
    Route::put('/workspaces/{workspace}/spaces/{space}/lists/{list}', [TaskListController::class, 'update'])
        ->name('lists.update');
    Route::delete('/workspaces/{workspace}/spaces/{space}/lists/{list}', [TaskListController::class, 'destroy'])
        ->name('lists.destroy');
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/duplicate', [TaskListController::class, 'duplicate'])
        ->name('lists.duplicate');

    // Statuses
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/statuses', [StatusController::class, 'store'])
        ->name('statuses.store');
    Route::put('/workspaces/{workspace}/spaces/{space}/lists/{list}/statuses/{status}', [StatusController::class, 'update'])
        ->name('statuses.update');
    Route::delete('/workspaces/{workspace}/spaces/{space}/lists/{list}/statuses/{status}', [StatusController::class, 'destroy'])
        ->name('statuses.destroy');
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/statuses/reorder', [StatusController::class, 'reorder'])
        ->name('statuses.reorder');

    // Tasks
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks', [TaskController::class, 'store'])
        ->name('tasks.store');
    Route::get('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}', [TaskController::class, 'show'])
        ->name('tasks.show');
    Route::put('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}', [TaskController::class, 'update'])
        ->name('tasks.update');
    Route::delete('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}', [TaskController::class, 'destroy'])
        ->name('tasks.destroy');
    Route::put('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/assignees', [TaskController::class, 'updateAssignees'])
        ->name('tasks.assignees');
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/reorder', [TaskController::class, 'reorder'])
        ->name('tasks.reorder');
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/move', [TaskController::class, 'move'])
        ->name('tasks.move');

    // Comments
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/comments', [CommentController::class, 'store'])
        ->name('comments.store');
    Route::put('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/comments/{comment}', [CommentController::class, 'update'])
        ->name('comments.update');
    Route::delete('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/comments/{comment}/resolve', [CommentController::class, 'resolve'])
        ->name('comments.resolve');
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/comments/{comment}/unresolve', [CommentController::class, 'unresolve'])
        ->name('comments.unresolve');

    // Checklists
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/checklists', [ChecklistController::class, 'store'])
        ->name('checklists.store');
    Route::put('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/checklists/{checklist}', [ChecklistController::class, 'update'])
        ->name('checklists.update');
    Route::delete('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/checklists/{checklist}', [ChecklistController::class, 'destroy'])
        ->name('checklists.destroy');
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/checklists/{checklist}/items', [ChecklistController::class, 'addItem'])
        ->name('checklists.items.store');
    Route::put('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/checklists/{checklist}/items/{item}', [ChecklistController::class, 'updateItem'])
        ->name('checklists.items.update');
    Route::delete('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/checklists/{checklist}/items/{item}', [ChecklistController::class, 'deleteItem'])
        ->name('checklists.items.destroy');
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/checklists/{checklist}/items/reorder', [ChecklistController::class, 'reorderItems'])
        ->name('checklists.items.reorder');

    // Time Entries
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/time-entries', [TimeEntryController::class, 'store'])
        ->name('time-entries.store');
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/time-entries/start', [TimeEntryController::class, 'startTimer'])
        ->name('time-entries.start');
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/time-entries/{timeEntry}/stop', [TimeEntryController::class, 'stopTimer'])
        ->name('time-entries.stop');
    Route::put('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/time-entries/{timeEntry}', [TimeEntryController::class, 'update'])
        ->name('time-entries.update');
    Route::delete('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/time-entries/{timeEntry}', [TimeEntryController::class, 'destroy'])
        ->name('time-entries.destroy');

    // Attachments
    Route::post('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/attachments', [AttachmentController::class, 'store'])
        ->name('attachments.store');
    Route::get('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');
    Route::delete('/workspaces/{workspace}/spaces/{space}/lists/{list}/tasks/{task}/attachments/{attachment}', [AttachmentController::class, 'destroy'])
        ->name('attachments.destroy');
});

require __DIR__.'/auth.php';
