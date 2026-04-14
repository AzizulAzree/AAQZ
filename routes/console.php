<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:create-user', function () {
    $name = text(
        label: 'Name',
        placeholder: 'Jane Doe',
        validate: fn (string $value) => filled($value) ? null : 'A name is required.',
    );

    $email = text(
        label: 'Email',
        placeholder: 'jane@example.com',
        validate: function (string $value) {
            if (blank($value)) {
                return 'An email address is required.';
            }

            if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return 'Please enter a valid email address.';
            }

            if (User::where('email', $value)->exists()) {
                return 'A user with that email already exists.';
            }

            return null;
        },
    );

    $newPassword = password(
        label: 'Password',
        validate: fn (string $value) => strlen($value) >= 8 ? null : 'The password must be at least 8 characters.',
    );

    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($newPassword),
    ]);

    $this->components->info("User {$user->email} created successfully.");
})->purpose('Create the first local user for this private app');
