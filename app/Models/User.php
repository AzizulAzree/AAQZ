<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\UserColor;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

#[Fillable(['name', 'email', 'password', 'color'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (! Schema::hasColumn($user->getTable(), 'color')) {
                return;
            }

            $user->color = UserColor::normalize($user->color)
                ?? UserColor::generateUnique(
                    static::query()->whereNotNull('color')->pluck('color')->all(),
                    max(1, (int) (static::query()->max('id') ?? 0) + 1),
                );
        });
    }

    public function isAdmin(): bool
    {
        $firstUserId = static::query()->orderBy('id')->value('id');

        return $firstUserId !== null && $this->id === $firstUserId;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function ownerColor(): string
    {
        return UserColor::normalize($this->color) ?? '#3B82F6';
    }

    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class)->orderBy('sort_order')->orderBy('name');
    }

    public function recentShortcuts(): HasMany
    {
        return $this->hasMany(RecentShortcut::class)->latest('opened_at');
    }

    public function stickyNote(): HasOne
    {
        return $this->hasOne(StickyNote::class);
    }
}
