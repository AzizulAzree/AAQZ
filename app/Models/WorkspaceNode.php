<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkspaceNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'parent_id',
        'type',
        'name',
        'url',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'workspace_id' => 'integer',
            'parent_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function recentShortcuts(): HasMany
    {
        return $this->hasMany(RecentShortcut::class);
    }

    public function isFolder(): bool
    {
        return $this->type === 'folder';
    }

    public function isShortcut(): bool
    {
        return $this->type === 'shortcut';
    }
}
