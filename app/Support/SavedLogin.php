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
        $record = $this->record($request);
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

        // EncryptCookies protects this cookie; only its token hash is stored in the database.
        Cookie::queue(Cookie::make(
            self::COOKIE, $id.'.'.$token, 90 * 24 * 60,
            '/', config('session.domain'), config('session.secure') ?? $request->isSecure(),
            true, false, 'lax',
        ));
    }

    public function forget(Request $request): void
    {
        if ($record = $this->record($request)) {
            DB::table('saved_logins')->where('id', $record->id)->delete();
        }
        Cookie::queue(Cookie::forget(self::COOKIE, '/', config('session.domain')));
    }

    private function record(Request $request): ?object
    {
        $value = $request->cookie(self::COOKIE);
        if (! is_string($value) || ! preg_match('/^([0-9a-f-]{36})\.([0-9a-f]{64})$/D', $value, $matches)) {
            return null;
        }
        $record = DB::table('saved_logins')->where('id', $matches[1])->first();

        return $record && hash_equals($record->token_hash, hash('sha256', $matches[2]))
            ? $record : null;
    }
}
