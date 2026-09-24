<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SavedLogin
{
    public const COOKIE = 'aaqz_saved_account';

    public function user(Request $request): ?User
    {
        return $this->userForToken($request->cookie(self::COOKIE));
    }

    public function userForToken(mixed $token): ?User
    {
        $record = $this->recordForToken($token);
        if (! $record || now()->greaterThanOrEqualTo($record->expires_at)) {
            return null;
        }

        $user = User::find($record->user_id);

        return $user && hash_equals($record->password_hash, hash('sha256', $user->getAuthPassword()))
            ? $user : null;
    }

    public function save(Request $request, User $user): void
    {
        $this->forget($request);
        $value = $this->issue($user);

        // EncryptCookies protects this cookie; only its token hash is stored in the database.
        Cookie::queue(Cookie::make(
            self::COOKIE, $value, 90 * 24 * 60,
            '/', config('session.domain'), config('session.secure') ?? $request->isSecure(),
            true, false, 'lax',
        ));
    }

    public function issue(User $user): string
    {
        DB::table('saved_logins')->where('expires_at', '<=', now())->delete();
        $id = (string) Str::uuid();
        $token = bin2hex(random_bytes(32));
        DB::table('saved_logins')->insert([
            'id' => $id,
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'password_hash' => hash('sha256', $user->getAuthPassword()),
            'expires_at' => now()->addDays(90),
        ]);

        return $id.'.'.$token;
    }

    public function forget(Request $request): void
    {
        $this->forgetToken($request->cookie(self::COOKIE));
        Cookie::queue(Cookie::forget(self::COOKIE, '/', config('session.domain')));
    }

    public function forgetToken(mixed $token): void
    {
        if ($record = $this->recordForToken($token)) {
            DB::table('saved_logins')->where('id', $record->id)->delete();
        }
    }

    private function recordForToken(mixed $value): ?object
    {
        if (! is_string($value) || ! preg_match('/^([0-9a-f-]{36})\.([0-9a-f]{64})$/D', $value, $matches)) {
            return null;
        }
        $record = DB::table('saved_logins')->where('id', $matches[1])->first();

        return $record && hash_equals($record->token_hash, hash('sha256', $matches[2]))
            ? $record : null;
    }
}
