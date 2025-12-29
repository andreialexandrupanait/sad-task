<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TaskList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'space_id',
        'folder_id',
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'position',
        'default_view',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TaskList $list) {
            if (empty($list->slug)) {
                $list->slug = Str::slug($list->name);
            }
        });

        static::created(function (TaskList $list) {
            // Create default statuses
            $list->statuses()->createMany([
                ['name' => 'To Do', 'color' => '#6b7280', 'type' => 'open', 'position' => 0, 'is_default' => true],
                ['name' => 'In Progress', 'color' => '#3b82f6', 'type' => 'in_progress', 'position' => 1],
                ['name' => 'Done', 'color' => '#22c55e', 'type' => 'done', 'position' => 2, 'is_closed' => true],
            ]);
        });
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class)->orderBy('position');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function rootTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereNull('parent_id');
    }

    public function customFields(): BelongsToMany
    {
        return $this->belongsToMany(CustomField::class, 'custom_field_list')
            ->withPivot('position', 'is_visible')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }

    public function getDefaultStatus(): ?Status
    {
        return $this->statuses()->where('is_default', true)->first()
            ?? $this->statuses()->first();
    }

    public function generateIdentifier(): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $this->name), 0, 3));
        $count = $this->tasks()->count() + 1;

        return "{$prefix}-{$count}";
    }
}
