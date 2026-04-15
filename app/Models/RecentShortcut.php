<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecentShortcut extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workspace_node_id',
        'opened_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'workspace_node_id' => 'integer',
            'opened_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspaceNode(): BelongsTo
    {
        return $this->belongsTo(WorkspaceNode::class);
    }
}
