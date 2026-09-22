<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div x-data="{ showPassword: @js(! $savedAccount || $errors->any()), forgetting: false }">
    @if ($savedAccount)
        <section class="saved-login" x-show="!showPassword">
            <h1 class="saved-login-heading">Welcome back</h1>
            <form method="POST" action="{{ route('login.saved') }}" x-data="{ busy: false }" @submit="busy = true">
                @csrf
                <button type="submit" class="saved-login-card" :disabled="busy">
                    <span class="saved-login-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($savedAccount->name, 0, 1)) }}</span>
                    <span class="saved-login-identity">
                        <strong>Continue as {{ $savedAccount->name }}</strong>
                        <span>{{ $savedAccount->email }}</span>
                    </span>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 5 7 7-7 7"/></svg>
                </button>
            </form>
            <div class="saved-login-actions">
                <button type="button" @click="showPassword = true; $nextTick(() => $refs.email.focus())">Use another account</button>
                <button type="button" @click="forgetting = !forgetting" :aria-expanded="forgetting">Forget this account</button>
            </div>
            <form method="POST" action="{{ route('login.saved.forget') }}" x-show="forgetting" x-cloak class="saved-login-forget">
                @csrf
                @method('DELETE')
                <p>Remove one-tap sign-in from this device? You’ll need your password next time.</p>
                <div><button type="button" @click="forgetting = false">Cancel</button><button type="submit">Remove account</button></div>
            </form>
        </section>
    @endif
    <form method="POST" action="{{ route('login') }}" x-show="showPassword" @if ($savedAccount && ! $errors->any()) x-cloak @endif>
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" x-ref="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-600" name="remember" value="1" @checked(old('remember'))>
                <span class="ms-2 text-sm text-gray-600">Save account on this device</span>
            </label>
            <p class="mt-1 text-xs text-gray-500">Anyone using this browser can sign in with your account card, even after you sign out.</p>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
    @if ($savedAccount)
        <button type="button" class="saved-login-back" x-show="showPassword" x-cloak @click="showPassword = false">Back to saved account</button>
    @endif
    <noscript><style>[x-cloak] { display: block !important; }</style></noscript>
    </div>
</x-guest-layout>
