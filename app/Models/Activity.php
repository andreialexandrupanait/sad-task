<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'subject_id',
        'subject_type',
        'type',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public const TYPE_CREATED = 'created';
    public const TYPE_UPDATED = 'updated';
    public const TYPE_DELETED = 'deleted';
    public const TYPE_ASSIGNED = 'assigned';
    public const TYPE_UNASSIGNED = 'unassigned';
    public const TYPE_STATUS_CHANGED = 'status_changed';
    public const TYPE_PRIORITY_CHANGED = 'priority_changed';
    public const TYPE_DUE_DATE_CHANGED = 'due_date_changed';
    public const TYPE_COMMENT_ADDED = 'comment_added';
    public const TYPE_ATTACHMENT_ADDED = 'attachment_added';
    public const TYPE_COMPLETED = 'completed';
    public const TYPE_REOPENED = 'reopened';

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(
        string $type,
        Model $subject,
        ?User $user = null,
        array $properties = []
    ): self {
        $workspaceId = null;

        if ($subject instanceof Task) {
            $workspaceId = $subject->taskList->space->workspace_id;
        } elseif ($subject instanceof Comment) {
            $workspaceId = $subject->task->taskList->space->workspace_id;
        } elseif (method_exists($subject, 'workspace')) {
            $workspaceId = $subject->workspace_id ?? $subject->workspace?->id;
        }

        return self::create([
            'workspace_id' => $workspaceId,
            'user_id' => $user?->id,
            'subject_id' => $subject->id,
            'subject_type' => get_class($subject),
            'type' => $type,
            'properties' => $properties,
        ]);
    }
}
