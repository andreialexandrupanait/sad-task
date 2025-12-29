<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'type',
        'options',
        'is_required',
        'default_value',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public const TYPE_TEXT = 'text';
    public const TYPE_NUMBER = 'number';
    public const TYPE_DATE = 'date';
    public const TYPE_DROPDOWN = 'dropdown';
    public const TYPE_CHECKBOX = 'checkbox';
    public const TYPE_URL = 'url';
    public const TYPE_EMAIL = 'email';
    public const TYPE_PHONE = 'phone';
    public const TYPE_CURRENCY = 'currency';
    public const TYPE_RATING = 'rating';
    public const TYPE_PROGRESS = 'progress';
    public const TYPE_LABEL = 'label';

    public static array $types = [
        self::TYPE_TEXT => 'Text',
        self::TYPE_NUMBER => 'Number',
        self::TYPE_DATE => 'Date',
        self::TYPE_DROPDOWN => 'Dropdown',
        self::TYPE_CHECKBOX => 'Checkbox',
        self::TYPE_URL => 'URL',
        self::TYPE_EMAIL => 'Email',
        self::TYPE_PHONE => 'Phone',
        self::TYPE_CURRENCY => 'Currency',
        self::TYPE_RATING => 'Rating',
        self::TYPE_PROGRESS => 'Progress',
        self::TYPE_LABEL => 'Label',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function taskLists(): BelongsToMany
    {
        return $this->belongsToMany(TaskList::class, 'custom_field_list')
            ->withPivot('position', 'is_visible')
            ->withTimestamps();
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
