<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class View extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_list_id',
        'user_id',
        'name',
        'type',
        'filters',
        'sorts',
        'columns',
        'settings',
        'is_default',
        'is_shared',
        'position',
    ];

    protected $casts = [
        'filters' => 'array',
        'sorts' => 'array',
        'columns' => 'array',
        'settings' => 'array',
        'is_default' => 'boolean',
        'is_shared' => 'boolean',
    ];

    public const TYPE_LIST = 'list';
    public const TYPE_BOARD = 'board';
    public const TYPE_CALENDAR = 'calendar';
    public const TYPE_GANTT = 'gantt';
    public const TYPE_TABLE = 'table';

    public static array $types = [
        self::TYPE_LIST => 'List',
        self::TYPE_BOARD => 'Board',
        self::TYPE_CALENDAR => 'Calendar',
        self::TYPE_GANTT => 'Gantt',
        self::TYPE_TABLE => 'Table',
    ];

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPersonal(): bool
    {
        return $this->user_id !== null && !$this->is_shared;
    }
}
