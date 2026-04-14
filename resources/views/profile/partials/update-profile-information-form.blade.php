<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account details and choose the color used to represent you across the site.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="color" :value="__('Your Color')" />
            <div class="mt-1 flex items-center gap-4">
                <input
                    id="color"
                    name="color"
                    type="color"
                    value="{{ old('color', $user->ownerColor()) }}"
                    class="h-12 w-16 cursor-pointer rounded-md border border-gray-300 bg-white p-1"
                />
                <div class="text-sm text-gray-600">
                    <p>{{ __('This color appears on your entries and account markers.') }}</p>
                    <p class="font-medium text-gray-900">{{ old('color', $user->ownerColor()) }}</p>
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('color')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
