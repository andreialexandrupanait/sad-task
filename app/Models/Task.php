<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_list_id',
        'status_id',
        'parent_id',
        'created_by',
        'title',
        'identifier',
        'description',
        'priority',
        'start_date',
        'due_date',
        'completed_at',
        'time_estimate',
        'time_spent',
        'position',
        'is_archived',
        'custom_fields',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'is_archived' => 'boolean',
        'custom_fields' => 'array',
    ];

    public const PRIORITY_URGENT = 1;
    public const PRIORITY_HIGH = 2;
    public const PRIORITY_NORMAL = 3;
    public const PRIORITY_LOW = 4;

    public static array $priorities = [
        self::PRIORITY_URGENT => 'Urgent',
        self::PRIORITY_HIGH => 'High',
        self::PRIORITY_NORMAL => 'Normal',
        self::PRIORITY_LOW => 'Low',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Task $task) {
            if (empty($task->identifier)) {
                $task->identifier = $task->taskList->generateIdentifier();
            }

            if (empty($task->status_id)) {
                $defaultStatus = $task->taskList->getDefaultStatus();
                if ($defaultStatus) {
                    $task->status_id = $defaultStatus->id;
                }
            }
        });
    }

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('position');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->wherePivot('role', 'assignee')
            ->withTimestamps();
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->wherePivot('role', 'watcher')
            ->withTimestamps();
    }

    public function tags(): MorphMany
    {
        return $this->morphMany(Taggable::class, 'taggable');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class)->orderBy('position');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_id')
            ->withPivot('type')
            ->withTimestamps();
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_id', 'task_id')
            ->withPivot('type')
            ->withTimestamps();
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null || ($this->status && $this->status->is_closed);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isCompleted();
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::$priorities[$this->priority] ?? 'Normal';
    }

    public function getProgressAttribute(): int
    {
        $subtasks = $this->subtasks;

        if ($subtasks->isEmpty()) {
            return $this->isCompleted() ? 100 : 0;
        }

        $completed = $subtasks->filter(fn($task) => $task->isCompleted())->count();

        return (int) round(($completed / $subtasks->count()) * 100);
    }
}
